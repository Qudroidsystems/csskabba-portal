<?php

namespace App\Http\Controllers;

use App\Models\DeviceOutageDate;
use App\Models\Staff;
use App\Models\StaffAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Staff attendance is purely device-driven — there is no manual register
 * UI. This controller is read-only reporting on top of rows written by
 * DeviceAttendanceProcessor::processStaff(), plus admin management of
 * device-outage dates so outages don't get counted as mass absence.
 */
class StaffAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View staff-attendance-school-report', ['only' => ['index']]);
        $this->middleware('permission:View staff-attendance-report',        ['only' => ['report']]);
        $this->middleware('permission:Create device-outages',               ['only' => ['storeOutage']]);
        $this->middleware('permission:Delete device-outages',               ['only' => ['destroyOutage']]);
    }

    // =========================================================================
    // SCHOOL-WIDE STAFF ATTENDANCE SUMMARY
    // =========================================================================

    public function index(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $outageDates = $this->getOutageDates($dateFrom, $dateTo);
        $workingDays = $this->getWorkingDays($dateFrom, $dateTo, $outageDates);

        // Aggregate per staff from raw device-driven rows — no separate summary
        // table needed at this volume (~200 staff).
        $records = StaffAttendance::whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->whereNotIn('attendance_date', $outageDates)
            ->select('staff_id')
            ->selectRaw("COUNT(CASE WHEN status = 'present' THEN 1 END) as days_present")
            ->selectRaw("COUNT(CASE WHEN status = 'late' THEN 1 END) as days_late")
            ->selectRaw("COUNT(CASE WHEN status = 'excused' THEN 1 END) as days_excused")
            ->groupBy('staff_id')
            ->get()
            ->keyBy('staff_id');

        $staffList = Staff::with('user')->active()->orderBy('id')->get();

        $rows = $staffList->map(function ($staff) use ($records, $workingDays) {
            $r        = $records->get($staff->id);
            $present  = (int) ($r->days_present ?? 0);
            $late     = (int) ($r->days_late ?? 0);
            $excused  = (int) ($r->days_excused ?? 0);
            $attended = $present + $late + $excused;
            $absent   = max($workingDays - $attended, 0); // inferred, never stored

            return (object) [
                'staff_id'              => $staff->id,
                'full_name'             => $staff->full_name,
                'employmentid'          => $staff->employmentid,
                'department'            => $staff->department,
                'days_present'          => $present,
                'days_late'             => $late,
                'days_excused'          => $excused,
                'days_absent'           => $absent,
                'attendance_percentage' => $workingDays > 0 ? round(($attended / $workingDays) * 100, 2) : 0,
            ];
        });

        $avgPct = $rows->count() > 0 ? round($rows->avg('attendance_percentage'), 1) : 0;

        $outages = DeviceOutageDate::whereBetween('outage_date', [$dateFrom, $dateTo])
            ->orderByDesc('outage_date')
            ->get();

        $pagetitle = 'Staff Attendance Report';

        return view('attendance.admin.staff-school-report', compact(
            'rows', 'workingDays', 'avgPct', 'dateFrom', 'dateTo', 'outages', 'pagetitle'
        ));
    }

    // =========================================================================
    // INDIVIDUAL STAFF DAILY LOG
    // =========================================================================

    public function report(Request $request, int $staffId)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $staff       = Staff::with('user')->findOrFail($staffId);
        $outageDates = $this->getOutageDates($dateFrom, $dateTo);

        $records = StaffAttendance::where('staff_id', $staffId)
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->whereNotIn('attendance_date', $outageDates)
            ->orderBy('attendance_date')
            ->get();

        $workingDays = $this->getWorkingDays($dateFrom, $dateTo, $outageDates);
        $present     = $records->where('status', 'present')->count();
        $late        = $records->where('status', 'late')->count();
        $excused     = $records->where('status', 'excused')->count();
        $attended    = $present + $late + $excused;
        $absent      = max($workingDays - $attended, 0);
        $pct         = $workingDays > 0 ? round(($attended / $workingDays) * 100, 2) : 0;

        $calendar = $this->buildCalendarWithRecords($dateFrom, $dateTo, $records, $outageDates);

        $pagetitle = "Attendance – {$staff->full_name}";

        return view('attendance.admin.staff-report', compact(
            'staff', 'records', 'calendar', 'workingDays',
            'present', 'late', 'excused', 'absent', 'pct',
            'dateFrom', 'dateTo', 'pagetitle'
        ));
    }

    // =========================================================================
    // DEVICE OUTAGE MANAGEMENT
    // =========================================================================

    public function storeOutage(Request $request)
    {
        $validated = $request->validate([
            'outage_date' => 'required|date',
            'reason'      => 'nullable|string|max:255',
        ]);
        $validated['marked_by'] = auth()->id();

        $outage = DeviceOutageDate::updateOrCreate(
            ['outage_date' => $validated['outage_date']],
            $validated
        );

        return response()->json(['success' => true, 'message' => 'Date marked as device outage.', 'data' => $outage]);
    }

    public function destroyOutage($id)
    {
        DeviceOutageDate::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Outage flag removed.']);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function resolveDateRange(Request $request): array
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->input('date_to', now()->toDateString());

        try {
            $dateFrom = Carbon::parse($dateFrom)->toDateString();
            $dateTo   = Carbon::parse($dateTo)->toDateString();
        } catch (\Exception $e) {
            $dateFrom = now()->startOfMonth()->toDateString();
            $dateTo   = now()->toDateString();
        }

        return [$dateFrom, $dateTo];
    }

    private function getOutageDates(string $dateFrom, string $dateTo): Collection
    {
        return DeviceOutageDate::whereBetween('outage_date', [$dateFrom, $dateTo])
            ->pluck('outage_date')
            ->map(fn($d) => $d->toDateString());
    }

    private function getWorkingDays(string $dateFrom, string $dateTo, ?Collection $outageDates = null): int
    {
        $outageDates ??= $this->getOutageDates($dateFrom, $dateTo);

        $count   = 0;
        $current = Carbon::parse($dateFrom);
        $end     = Carbon::parse($dateTo);

        while ($current->lte($end)) {
            if (!$current->isWeekend() && $current->lte(now()) && !$outageDates->contains($current->toDateString())) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    private function buildCalendarWithRecords(string $dateFrom, string $dateTo, $records, ?Collection $outageDates = null): array
    {
        $outageDates ??= $this->getOutageDates($dateFrom, $dateTo);
        $byDate       = $records->keyBy(fn($r) => $r->attendance_date->toDateString());

        $days    = [];
        $current = Carbon::parse($dateFrom);
        $end     = Carbon::parse($dateTo);

        while ($current->lte($end) && $current->lte(now())) {
            if (!$current->isWeekend()) {
                $key = $current->toDateString();

                if ($outageDates->contains($key)) {
                    $days[] = [
                        'date' => $key, 'label' => $current->format('D, d M'),
                        'status' => 'outage', 'time_in' => null, 'time_out' => null,
                    ];
                } else {
                    $rec = $byDate->get($key);
                    $days[] = [
                        'date'     => $key,
                        'label'    => $current->format('D, d M'),
                        'status'   => $rec->status ?? 'absent', // inferred when no device record exists
                        'time_in'  => $rec?->time_in,
                        'time_out' => $rec?->time_out,
                    ];
                }
            }
            $current->addDay();
        }

        return $days;
    }
}
