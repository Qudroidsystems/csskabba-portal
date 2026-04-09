<?php
// app/Http/Controllers/TimetableReportController.php

namespace App\Http\Controllers;

use App\Models\TimetableReport;
use App\Models\TimetableSetting;
use App\Models\TimetableSlot;
use App\Models\User;
use App\Models\Room;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TimetableReportController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:View timetable reports', ['only' => ['index', 'show', 'download']]);
        // $this->middleware('permission:Generate timetable reports', ['only' => ['generate', 'schedule']]);
        // $this->middleware('permission:Delete timetable reports', ['only' => ['destroy']]);
    }

    public function index()
    {
        $pagetitle = 'Timetable Reports';
        $reports = TimetableReport::with('generator')->orderBy('created_at', 'desc')->paginate(15);
        $sessions = Schoolsession::orderByDesc('id')->get();
        $terms = Schoolterm::all();

        return view('timetable.reports', compact('pagetitle', 'reports', 'sessions', 'terms'));
    }

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'report_type' => 'required|in:teacher_workload,room_utilization,class_schedule,conflict_analysis,subject_distribution',
            'session_id' => 'nullable|exists:schoolsession,id',
            'term_id' => 'nullable|exists:schoolterm,id',
            'format' => 'in:json,csv,pdf',
        ]);

        $sessionId = $validated['session_id'] ?? Schoolsession::where('status', 'Current')->value('id');
        $format = $validated['format'] ?? 'json';

        $data = $this->generateReportData($validated['report_type'], $sessionId, $validated['term_id'] ?? null);

        $reportName = ucfirst(str_replace('_', ' ', $validated['report_type'])) . ' Report — ' . now()->format('Y-m-d H:i');

        $report = TimetableReport::create([
            'report_name' => $reportName,
            'report_type' => $validated['report_type'],
            'session_id' => $sessionId,
            'term_id' => $validated['term_id'] ?? null,
            'filters' => $validated,
            'data' => $data,
            'generated_by' => Auth::id(),
        ]);

        if ($format === 'csv') {
            return $this->exportToCsv($report, $data);
        }

        if ($format === 'pdf') {
            return $this->exportToPdf($report, $data);
        }

        return response()->json([
            'success' => true,
            'report' => $report,
            'data' => $data,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $report = TimetableReport::with('generator')->findOrFail($id);
        return response()->json(['success' => true, 'report' => $report]);
    }

    public function download(Request $request, int $id)
    {
        $report = TimetableReport::findOrFail($id);
        $format = $request->input('format', 'csv');

        if ($format === 'pdf') {
            return $this->exportToPdf($report, $report->data);
        }

        if ($report->file_path && Storage::exists($report->file_path)) {
            return Storage::download($report->file_path, $report->report_name . '.csv');
        }

        return $this->exportToCsv($report, $report->data);
    }

    public function destroy(int $id): JsonResponse
    {
        $report = TimetableReport::findOrFail($id);

        if ($report->file_path && Storage::exists($report->file_path)) {
            Storage::delete($report->file_path);
        }

        $report->delete();
        return response()->json(['success' => true, 'message' => 'Report deleted successfully']);
    }

    // =========================================================================
    // REPORT DATA GENERATORS
    // =========================================================================

    private function generateReportData(string $reportType, ?int $sessionId, ?int $termId): array
    {
        return match($reportType) {
            'teacher_workload'    => $this->getTeacherWorkloadReport($sessionId, $termId),
            'room_utilization'    => $this->getRoomUtilizationReport($sessionId, $termId),
            'class_schedule'      => $this->getClassScheduleReport($sessionId, $termId),
            'conflict_analysis'   => $this->getConflictAnalysisReport($sessionId, $termId),
            'subject_distribution'=> $this->getSubjectDistributionReport($sessionId, $termId),
            default               => [],
        };
    }

    private function getTeacherWorkloadReport(?int $sessionId, ?int $termId): array
    {
        $teachers = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->get();
        $report = [];

        foreach ($teachers as $teacher) {
            $slots = TimetableSlot::where('teacher_id', $teacher->id)
                ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
                ->with(['period', 'subject', 'setting.schoolclass'])
                ->get();

            $dailyDistribution = [];
            foreach (TimetableController::DAYS as $day) {
                $dailyDistribution[$day] = $slots->where('day', $day)->count();
            }

            $report[] = [
                'teacher_name'      => $teacher->name,
                'teacher_email'     => $teacher->email,
                'total_periods'     => $slots->count(),
                'monday'            => $dailyDistribution['Monday'],
                'tuesday'           => $dailyDistribution['Tuesday'],
                'wednesday'         => $dailyDistribution['Wednesday'],
                'thursday'          => $dailyDistribution['Thursday'],
                'friday'            => $dailyDistribution['Friday'],
                'subjects_taught'   => $slots->pluck('subject.subject')->filter()->unique()->implode(', '),
                'classes_taught'    => $slots->pluck('setting.schoolclass.schoolclass')->filter()->unique()->implode(', '),
                'total_classes'     => $slots->pluck('setting.schoolclass_id')->unique()->count(),
            ];
        }

        return $report;
    }

    private function getRoomUtilizationReport(?int $sessionId, ?int $termId): array
    {
        $rooms = Room::where('is_active', true)->get();
        $report = [];

        foreach ($rooms as $room) {
            $bookings = TimetableSlot::where('room_id', $room->id)
                ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
                ->get();

            $utilizationByDay = [];
            foreach (TimetableController::DAYS as $day) {
                $count = $bookings->where('day', $day)->count();
                $utilizationByDay[$day] = [
                    'count' => $count,
                    'percentage' => min(100, round(($count / 8) * 100)),
                ];
            }

            $avgUtil = $bookings->count() > 0
                ? round(collect($utilizationByDay)->avg('percentage'), 1)
                : 0;

            $report[] = [
                'room_name'           => $room->room_name,
                'room_code'           => $room->room_code,
                'type'                => $room->type,
                'capacity'            => $room->capacity,
                'total_bookings'      => $bookings->count(),
                'monday_count'        => $utilizationByDay['Monday']['count'],
                'tuesday_count'       => $utilizationByDay['Tuesday']['count'],
                'wednesday_count'     => $utilizationByDay['Wednesday']['count'],
                'thursday_count'      => $utilizationByDay['Thursday']['count'],
                'friday_count'        => $utilizationByDay['Friday']['count'],
                'average_utilization' => $avgUtil . '%',
            ];
        }

        return $report;
    }

    private function getClassScheduleReport(?int $sessionId, ?int $termId): array
    {
        $settings = TimetableSetting::where('session_id', $sessionId)
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->with(['schoolclass', 'session', 'term', 'periods', 'slots.subject', 'slots.teacher'])
            ->get();

        $report = [];
        foreach ($settings as $setting) {
            $totalLessonSlots = $setting->periods->where('type', 'lesson')->count() * count($setting->active_days ?? TimetableController::DAYS);
            $filledSlots = $setting->slots->whereNotNull('subject_id')->count();

            $report[] = [
                'class'               => $setting->schoolclass->schoolclass ?? 'Unknown',
                'session'             => $setting->session->session ?? '',
                'term'                => $setting->term->term ?? 'All Terms',
                'total_lesson_slots'  => $totalLessonSlots,
                'filled_slots'        => $filledSlots,
                'free_slots'          => max(0, $totalLessonSlots - $filledSlots),
                'completion_percent'  => $totalLessonSlots > 0
                    ? round(($filledSlots / $totalLessonSlots) * 100, 1) . '%'
                    : '0%',
                'subjects_count'      => $setting->slots->pluck('subject_id')->filter()->unique()->count(),
                'teachers_count'      => $setting->slots->pluck('teacher_id')->filter()->unique()->count(),
            ];
        }

        return $report;
    }

    private function getConflictAnalysisReport(?int $sessionId, ?int $termId): array
    {
        $slots = TimetableSlot::whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
            ->whereNotNull('teacher_id')
            ->with(['period', 'teacher', 'setting.schoolclass', 'subject'])
            ->get();

        $conflicts = [];
        $teacherSlotMap = [];

        foreach ($slots as $slot) {
            $key = $slot->teacher_id . '_' . $slot->day . '_' . $slot->period_id;
            if (isset($teacherSlotMap[$key])) {
                $conflicts[] = [
                    'teacher'     => $slot->teacher->name ?? 'Unknown',
                    'teacher_email' => $slot->teacher->email ?? '',
                    'day'         => $slot->day,
                    'period'      => $slot->period->name ?? '',
                    'period_time' => ($slot->period->start_time ?? '') . ' - ' . ($slot->period->end_time ?? ''),
                    'class_a'     => $teacherSlotMap[$key]->setting->schoolclass->schoolclass ?? '',
                    'subject_a'   => $teacherSlotMap[$key]->subject->subject ?? '',
                    'class_b'     => $slot->setting->schoolclass->schoolclass ?? '',
                    'subject_b'   => $slot->subject->subject ?? '',
                ];
            } else {
                $teacherSlotMap[$key] = $slot;
            }
        }

        return [
            'summary' => [
                'total_conflicts'    => count($conflicts),
                'affected_teachers'  => collect($conflicts)->pluck('teacher')->unique()->count(),
                'affected_days'      => collect($conflicts)->pluck('day')->unique()->implode(', '),
            ],
            'conflicts' => $conflicts,
        ];
    }

    private function getSubjectDistributionReport(?int $sessionId, ?int $termId): array
    {
        $slots = TimetableSlot::whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
            ->whereNotNull('subject_id')
            ->with(['subject', 'setting.schoolclass'])
            ->get();

        $subjectCount = [];
        foreach ($slots as $slot) {
            $subjectName = $slot->subject->subject ?? 'Unknown';
            $className = $slot->setting->schoolclass->schoolclass ?? 'Unknown';
            $key = $subjectName . '||' . $className;

            $subjectCount[$key] = ($subjectCount[$key] ?? 0) + 1;
        }

        $report = [];
        foreach ($subjectCount as $key => $count) {
            [$subject, $class] = explode('||', $key, 2);
            $report[] = [
                'subject'          => $subject,
                'class'            => $class,
                'periods_per_week' => $count,
            ];
        }

        // Sort by subject name
        usort($report, fn($a, $b) => strcmp($a['subject'], $b['subject']));

        return $report;
    }

    // =========================================================================
    // EXPORT HELPERS
    // =========================================================================

    private function exportToCsv(TimetableReport $report, $data)
    {
        $filename = str_replace([' ', '—', '/'], ['_', '-', '_'], $report->report_name) . '.csv';
        $handle = fopen('php://temp', 'w+');

        // Flatten nested structures
        $flatData = $this->flattenForCsv($data, $report->report_type);

        if (!empty($flatData)) {
            $headers = array_keys($flatData[0]);
            fputcsv($handle, $headers);
            foreach ($flatData as $row) {
                fputcsv($handle, array_map(fn($v) => is_array($v) ? implode(', ', $v) : $v, $row));
            }
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        // Cache to disk for future downloads
        $filePath = 'reports/' . $filename;
        Storage::put($filePath, $csv);
        $report->update(['file_path' => $filePath]);

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    private function exportToPdf(TimetableReport $report, $data)
    {
        $html = $this->buildReportHtml($report, $data);

        // Try DomPDF
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait');
            $filename = str_replace([' ', '—'], ['_', '-'], $report->report_name) . '.pdf';
            return $pdf->download($filename);
        }

        // Fallback: printable HTML
        return response($html, 200)->header('Content-Type', 'text/html');
    }

    private function buildReportHtml(TimetableReport $report, $data): string
    {
        $title = htmlspecialchars($report->report_name);
        $generated = now()->format('d M Y H:i');
        $flatData = $this->flattenForCsv($data, $report->report_type);

        $tableHeaders = '';
        $tableRows = '';

        if (!empty($flatData)) {
            $headers = array_keys($flatData[0]);
            foreach ($headers as $h) {
                $tableHeaders .= '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $h))) . '</th>';
            }
            foreach ($flatData as $row) {
                $tableRows .= '<tr>';
                foreach ($row as $val) {
                    $display = is_array($val) ? implode(', ', $val) : ($val ?? '—');
                    $tableRows .= '<td>' . htmlspecialchars((string)$display) . '</td>';
                }
                $tableRows .= '</tr>';
            }
        }

        return "<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<title>{$title}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #333; }
  .header { border-bottom: 3px solid #1a1a2e; padding-bottom: 12px; margin-bottom: 20px; }
  .header h1 { font-size: 16px; color: #1a1a2e; }
  .header .meta { font-size: 10px; color: #666; margin-top: 4px; }
  table { width: 100%; border-collapse: collapse; margin-top: 12px; }
  th { background: #1a1a2e; color: #fff; padding: 7px 8px; text-align: left; font-size: 10px; }
  td { border: 1px solid #ddd; padding: 6px 8px; font-size: 10px; }
  tr:nth-child(even) td { background: #f9f9f9; }
  .footer { margin-top: 20px; font-size: 9px; color: #aaa; text-align: right; }
  @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
</style>
</head>
<body>
  <div class='header'>
    <h1>{$title}</h1>
    <div class='meta'>Generated: {$generated}</div>
  </div>
  <table>
    <thead><tr>{$tableHeaders}</tr></thead>
    <tbody>{$tableRows}</tbody>
  </table>
  <div class='footer'>Timetable Management System &mdash; {$generated}</div>
</body>
</html>";
    }

    /**
     * Normalize report data to a flat array-of-arrays for CSV/PDF export.
     * Handles the conflict_analysis report which has a nested summary + conflicts structure.
     */
    private function flattenForCsv($data, string $reportType): array
    {
        if (empty($data)) return [];

        // Conflict analysis has a nested structure
        if ($reportType === 'conflict_analysis') {
            return $data['conflicts'] ?? [];
        }

        // If first element is an array, it's already flat rows
        if (isset($data[0]) && is_array($data[0])) {
            return $data;
        }

        return $data;
    }
}
