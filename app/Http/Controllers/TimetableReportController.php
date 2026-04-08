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
    //     $this->middleware('permission:View timetable reports', ['only' => ['index', 'show', 'download']]);
    //     $this->middleware('permission:Generate timetable reports', ['only' => ['generate', 'schedule']]);
    //     $this->middleware('permission:Delete timetable reports', ['only' => ['destroy']]);
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

        // Save report record
        $report = TimetableReport::create([
            'report_name' => ucfirst(str_replace('_', ' ', $validated['report_type'])) . ' Report - ' . now()->format('Y-m-d H:i'),
            'report_type' => $validated['report_type'],
            'session_id' => $sessionId,
            'term_id' => $validated['term_id'],
            'filters' => $validated,
            'data' => $data,
            'generated_by' => Auth::id(),
        ]);

        if ($format === 'csv') {
            return $this->exportToCsv($report, $data);
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

    public function download(int $id)
    {
        $report = TimetableReport::findOrFail($id);

        if ($report->file_path && Storage::exists($report->file_path)) {
            return Storage::download($report->file_path, $report->report_name . '.csv');
        }

        // Generate CSV on the fly
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

    private function generateReportData($reportType, $sessionId, $termId)
    {
        switch ($reportType) {
            case 'teacher_workload':
                return $this->getTeacherWorkloadReport($sessionId, $termId);
            case 'room_utilization':
                return $this->getRoomUtilizationReport($sessionId, $termId);
            case 'class_schedule':
                return $this->getClassScheduleReport($sessionId, $termId);
            case 'conflict_analysis':
                return $this->getConflictAnalysisReport($sessionId, $termId);
            case 'subject_distribution':
                return $this->getSubjectDistributionReport($sessionId, $termId);
            default:
                return [];
        }
    }

    private function getTeacherWorkloadReport($sessionId, $termId): array
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
                'teacher_name' => $teacher->name,
                'teacher_email' => $teacher->email,
                'total_periods' => $slots->count(),
                'daily_distribution' => $dailyDistribution,
                'subjects_taught' => $slots->pluck('subject.subject')->unique()->values(),
                'classes_taught' => $slots->pluck('setting.schoolclass.schoolclass')->unique()->values(),
                'total_classes' => $slots->pluck('setting.schoolclass_id')->unique()->count(),
            ];
        }

        return $report;
    }

    private function getRoomUtilizationReport($sessionId, $termId): array
    {
        $rooms = Room::where('is_active', true)->get();
        $report = [];

        foreach ($rooms as $room) {
            $bookings = TimetableSlot::where('room_id', $room->id)
                ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
                ->get();

            $utilizationByDay = [];
            foreach (TimetableController::DAYS as $day) {
                $dayBookings = $bookings->where('day', $day);
                $utilizationByDay[$day] = [
                    'count' => $dayBookings->count(),
                    'percentage' => round(($dayBookings->count() / 8) * 100),
                ];
            }

            $report[] = [
                'room_name' => $room->room_name,
                'room_code' => $room->room_code,
                'type' => $room->type,
                'capacity' => $room->capacity,
                'total_bookings' => $bookings->count(),
                'utilization_by_day' => $utilizationByDay,
                'average_utilization' => round(collect($utilizationByDay)->avg('percentage'), 2),
            ];
        }

        return $report;
    }

    private function getClassScheduleReport($sessionId, $termId): array
    {
        $settings = TimetableSetting::where('session_id', $sessionId)
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->with(['schoolclass', 'periods', 'slots.subject', 'slots.teacher'])
            ->get();

        $report = [];
        foreach ($settings as $setting) {
            $report[] = [
                'class' => $setting->schoolclass->schoolclass ?? 'Unknown',
                'session' => $setting->session->session ?? '',
                'term' => $setting->term->term ?? 'All Terms',
                'total_periods' => $setting->periods->count(),
                'total_slots_filled' => $setting->slots->whereNotNull('subject_id')->count(),
                'completion_percentage' => round(($setting->slots->whereNotNull('subject_id')->count() / max($setting->periods->count() * 5, 1)) * 100),
            ];
        }

        return $report;
    }

    private function getConflictAnalysisReport($sessionId, $termId): array
    {
        $slots = TimetableSlot::whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
            ->whereNotNull('teacher_id')
            ->with(['period', 'teacher', 'setting.schoolclass'])
            ->get();

        $conflicts = [];
        $teacherSlotMap = [];

        foreach ($slots as $slot) {
            $key = $slot->teacher_id . '_' . $slot->day . '_' . $slot->period_id;
            if (isset($teacherSlotMap[$key])) {
                $conflicts[] = [
                    'teacher' => $slot->teacher->name,
                    'day' => $slot->day,
                    'period' => $slot->period->name,
                    'class_a' => $teacherSlotMap[$key]->setting->schoolclass->schoolclass,
                    'class_b' => $slot->setting->schoolclass->schoolclass,
                ];
            } else {
                $teacherSlotMap[$key] = $slot;
            }
        }

        return [
            'total_conflicts' => count($conflicts),
            'conflicts' => $conflicts,
            'affected_teachers' => collect($conflicts)->pluck('teacher')->unique()->count(),
        ];
    }

    private function getSubjectDistributionReport($sessionId, $termId): array
    {
        $slots = TimetableSlot::whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
            ->whereNotNull('subject_id')
            ->with(['subject', 'setting.schoolclass'])
            ->get();

        $subjectCount = [];
        foreach ($slots as $slot) {
            $subjectName = $slot->subject->subject ?? 'Unknown';
            $className = $slot->setting->schoolclass->schoolclass ?? 'Unknown';
            $key = $subjectName . '_' . $className;

            if (!isset($subjectCount[$key])) {
                $subjectCount[$key] = 0;
            }
            $subjectCount[$key]++;
        }

        $report = [];
        foreach ($subjectCount as $key => $count) {
            list($subject, $class) = explode('_', $key);
            $report[] = [
                'subject' => $subject,
                'class' => $class,
                'periods_per_week' => $count,
            ];
        }

        return $report;
    }

    private function exportToCsv($report, $data)
    {
        $filename = str_replace(' ', '_', $report->report_name) . '.csv';
        $handle = fopen('php://temp', 'w+');

        // Add headers based on report type
        if (!empty($data)) {
            $headers = array_keys((array)$data[0]);
            fputcsv($handle, $headers);

            foreach ($data as $row) {
                fputcsv($handle, (array)$row);
            }
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        // Save file path for future downloads
        $filePath = 'reports/' . $filename;
        Storage::put($filePath, $csv);
        $report->update(['file_path' => $filePath]);

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
