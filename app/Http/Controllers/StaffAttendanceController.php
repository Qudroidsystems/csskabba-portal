<?php

namespace App\Http\Controllers;

use App\Exports\StaffAttendanceExport;
use App\Models\DeviceOutageDate;
use App\Models\Staff;
use App\Models\StaffAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Staff attendance is purely device-driven — there is no manual register
 * UI. This controller is read-only reporting on top of rows written by
 * DeviceAttendanceProcessor::processStaff(), plus admin management of
 * device-outage dates so outages don't get counted as mass absence.
 *
 * Also supports the report's Exclude Days panel: weekdays ticked there
 * (submitted as excluded_weekdays[] on the GET request, ISO weekday
 * numbers 1=Monday..7=Sunday) exclude every date in the selected range
 * that falls on that weekday. Unlike DeviceOutageDate, these are NOT
 * persisted — they only apply to the report being viewed right now.
 * Saturday and Sunday are ordinary working days unless the admin
 * explicitly ticks them here — nothing is excluded by default.
 */
class StaffAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:View staff-attendance-school-report', ['only' => ['index', 'exportExcel']]);
        $this->middleware('permission:View staff-attendance-report',        ['only' => ['report']]);
        $this->middleware('permission:Create device-outages',               ['only' => ['storeOutage']]);
        $this->middleware('permission:Delete device-outages',               ['only' => ['destroyOutage']]);
    }

    // =========================================================================
    // SCHOOL-WIDE STAFF ATTENDANCE SUMMARY
    // =========================================================================

    public function index(Request $request)
    {
        $data = $this->buildSchoolReportData($request);

        return view('attendance.admin.staff-school-report', $data + ['pagetitle' => 'Staff Attendance Report']);
    }

    // =========================================================================
    // EXCEL EXPORT
    // =========================================================================

    /**
     * Renders through the exact same buildSchoolReportData() as index(), so
     * the download can never show different numbers than what's on screen —
     * same date range, same excluded weekdays, same query.
     */
    public function exportExcel(Request $request)
    {
        $data = $this->buildSchoolReportData($request);

        $filename = "staff-attendance-{$data['dateFrom']}-to-{$data['dateTo']}.xlsx";

        return Excel::download(
            new StaffAttendanceExport($data['rows'], $data['dateFrom'], $data['dateTo']),
            $filename
        );
    }

    /**
     * Shared by index() and exportExcel(). Returns everything the
     * staff-school-report view (and the export) needs, keyed to match the
     * view's expected variable names.
     */
    private function buildSchoolReportData(Request $request): array
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $excludedDates = $this->resolveExcludedDates($request, $dateFrom, $dateTo);
        $workingDays   = $this->getWorkingDays($dateFrom, $dateTo, $excludedDates);

        // Aggregate per staff from raw device-driven rows — no separate summary
        // table needed at this volume (~200 staff).
        $records = StaffAttendance::whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->whereNotIn('attendance_date', $excludedDates)
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

        // Only persisted device outages show in the outage-badge list —
        // ad-hoc excluded weekdays are request-scoped and intentionally don't
        // appear there (see resolveExcludedDates() docblock).
        $outages = DeviceOutageDate::whereBetween('outage_date', [$dateFrom, $dateTo])
            ->orderByDesc('outage_date')
            ->get();

        return compact('rows', 'workingDays', 'avgPct', 'dateFrom', 'dateTo', 'outages');
    }

    // =========================================================================
    // INDIVIDUAL STAFF DAILY LOG
    // =========================================================================

    public function report(Request $request, int $staffId)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $staff         = Staff::with('user')->findOrFail($staffId);
        $excludedDates = $this->resolveExcludedDates($request, $dateFrom, $dateTo);

        $records = StaffAttendance::where('staff_id', $staffId)
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->whereNotIn('attendance_date', $excludedDates)
            ->orderBy('attendance_date')
            ->get();

        $workingDays = $this->getWorkingDays($dateFrom, $dateTo, $excludedDates);
        $present     = $records->where('status', 'present')->count();
        $late        = $records->where('status', 'late')->count();
        $excused     = $records->where('status', 'excused')->count();
        $attended    = $present + $late + $excused;
        $absent      = max($workingDays - $attended, 0);
        $pct         = $workingDays > 0 ? round(($attended / $workingDays) * 100, 2) : 0;

        $calendar = $this->buildCalendarWithRecords($dateFrom, $dateTo, $records, $excludedDates);

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

    /**
     * Reads excluded_weekdays[] from the request — ISO weekday numbers
     * (1 = Monday ... 7 = Sunday) ticked in the report's Exclude Days
     * panel. Invalid/out-of-range values are silently dropped; this is a
     * convenience UI control, not a place that needs strict validation
     * errors surfaced back to the admin.
     */
    private function resolveExcludedWeekdays(Request $request): array
    {
        return collect($request->input('excluded_weekdays', []))
            ->map(fn($d) => (int) $d)
            ->filter(fn($d) => $d >= 1 && $d <= 7)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Merges persisted device outages with every date in the selected range
     * that falls on a weekday ticked in the Exclude Days panel. A single
     * "Monday" tick expands to every Monday between $dateFrom and $dateTo —
     * the admin doesn't have to pick individual dates.
     */
    private function resolveExcludedDates(Request $request, string $dateFrom, string $dateTo): Collection
    {
        $deviceOutages    = $this->getOutageDates($dateFrom, $dateTo);
        $excludedWeekdays = $this->resolveExcludedWeekdays($request);

        $weekdayDates = collect();

        if (!empty($excludedWeekdays)) {
            $cursor = Carbon::parse($dateFrom);
            $end    = Carbon::parse($dateTo);

            while ($cursor->lte($end)) {
                if (in_array($cursor->isoWeekday(), $excludedWeekdays, true)) {
                    $weekdayDates->push($cursor->toDateString());
                }
                $cursor->addDay();
            }
        }

        return $deviceOutages->merge($weekdayDates)->unique()->values();
    }

    /**
     * Every date in the range counts as a working day by default —
     * including Saturday and Sunday — unless it's a persisted device
     * outage or falls on a weekday the admin excluded for this view.
     */
    private function getWorkingDays(string $dateFrom, string $dateTo, ?Collection $excludedDates = null): int
    {
        $excludedDates ??= $this->getOutageDates($dateFrom, $dateTo);

        $count   = 0;
        $current = Carbon::parse($dateFrom);
        $end     = Carbon::parse($dateTo);

        while ($current->lte($end)) {
            if ($current->lte(now()) && !$excludedDates->contains($current->toDateString())) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    private function buildCalendarWithRecords(string $dateFrom, string $dateTo, $records, ?Collection $excludedDates = null): array
    {
        $excludedDates ??= $this->getOutageDates($dateFrom, $dateTo);
        $deviceOutages  = $this->getOutageDates($dateFrom, $dateTo); // distinguishes real outages from weekday-excludes below
        $byDate         = $records->keyBy(fn($r) => $r->attendance_date->toDateString());

        $days    = [];
        $current = Carbon::parse($dateFrom);
        $end     = Carbon::parse($dateTo);

        // No weekend skip here anymore — Saturday/Sunday punches (including
        // late ones) now show up like any other day unless excluded above.
        while ($current->lte($end) && $current->lte(now())) {
            $key = $current->toDateString();

            if ($excludedDates->contains($key)) {
                $days[] = [
                    'date'   => $key,
                    'label'  => $current->format('D, d M'),
                    // 'outage' = flagged device downtime, 'excluded' = admin ticked
                    // this weekday in the report's Exclude Days panel for this view only.
                    'status'   => $deviceOutages->contains($key) ? 'outage' : 'excluded',
                    'time_in'  => null,
                    'time_out' => null,
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

            $current->addDay();
        }

        return $days;
    }
}