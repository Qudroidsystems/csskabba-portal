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
 * Exclusions come from three sources, all merged into one date set for
 * every query/count/export in this controller:
 *
 *   1. DeviceOutageDate  — persisted, admin-managed device downtime.
 *   2. Weekday pattern   — excluded_weekdays[] (0=Sun..6=Sat) on the
 *      request. Defaults to [0, 6] (Sat/Sun) when the filter form hasn't
 *      been submitted yet, matching the old hardcoded isWeekend() behavior.
 *      Once submitted (weekday_filter_submitted=1 is present), whatever
 *      the admin actually ticked is used verbatim — including an empty
 *      set, which means "don't exclude any weekday".
 *   3. Ad-hoc dates      — excluded_dates[] on the request, from the
 *      report's Exclude Days panel. Request-scoped, never persisted.
 *
 * Weekday-pattern dates are hidden from the daily calendar entirely (they
 * never had a row before either). Outage/ad-hoc dates still render as a
 * visible row labeled 'outage' or 'excluded' so it's clear why a specific
 * date has no attendance data.
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
     * same date range, same excluded weekdays, same excluded dates.
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

        $excl        = $this->resolveExclusions($request, $dateFrom, $dateTo);
        $workingDays = $this->getWorkingDays($dateFrom, $dateTo, $excl['all']);

        // Aggregate per staff from raw device-driven rows — no separate summary
        // table needed at this volume (~200 staff).
        $records = StaffAttendance::whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->whereNotIn('attendance_date', $excl['all'])
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
                'avatar_url'            => $staff->user?->avatar_url,
                'days_present'          => $present,
                'days_late'             => $late,
                'days_excused'          => $excused,
                'days_absent'           => $absent,
                'attendance_percentage' => $workingDays > 0 ? round(($attended / $workingDays) * 100, 2) : 0,
            ];
        });

        $avgPct = $rows->count() > 0 ? round($rows->avg('attendance_percentage'), 1) : 0;

        // Only persisted device outages show in the outage-badge list —
        // weekday-pattern and ad-hoc excludes are request-scoped and
        // intentionally don't appear there.
        $outages = DeviceOutageDate::whereBetween('outage_date', [$dateFrom, $dateTo])
            ->orderByDesc('outage_date')
            ->get();

        return compact('rows', 'workingDays', 'avgPct', 'dateFrom', 'dateTo', 'outages')
            + ['excludedWeekdays' => $excl['weekdays']];
    }

    // =========================================================================
    // INDIVIDUAL STAFF DAILY LOG
    // =========================================================================

    public function report(Request $request, int $staffId)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $staff = Staff::with('user')->findOrFail($staffId);
        $excl  = $this->resolveExclusions($request, $dateFrom, $dateTo);

        $records = StaffAttendance::where('staff_id', $staffId)
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->whereNotIn('attendance_date', $excl['all'])
            ->orderBy('attendance_date')
            ->get();

        $workingDays = $this->getWorkingDays($dateFrom, $dateTo, $excl['all']);
        $present     = $records->where('status', 'present')->count();
        $late        = $records->where('status', 'late')->count();
        $excused     = $records->where('status', 'excused')->count();
        $attended    = $present + $late + $excused;
        $absent      = max($workingDays - $attended, 0);
        $pct         = $workingDays > 0 ? round(($attended / $workingDays) * 100, 2) : 0;

        $calendar = $this->buildCalendarWithRecords($dateFrom, $dateTo, $records, $excl);

        $pagetitle = "Attendance – {$staff->full_name}";

        return view('attendance.admin.staff-report', compact(
            'staff', 'records', 'calendar', 'workingDays',
            'present', 'late', 'excused', 'absent', 'pct',
            'dateFrom', 'dateTo', 'pagetitle'
        ) + ['excludedWeekdays' => $excl['weekdays']]);
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
     * Which weekdays (0=Sun..6=Sat, matching Carbon::dayOfWeek) are excluded
     * for this request. Defaults to [0, 6] — Sunday & Saturday — UNLESS the
     * filter form was actually submitted (weekday_filter_submitted=1),
     * in which case excluded_weekdays[] is used exactly as given, even if
     * that means an empty array (admin wants every day of the week counted).
     */
    private function resolveExcludedWeekdays(Request $request): Collection
    {
        if (!$request->has('weekday_filter_submitted')) {
            return collect([0, 6]);
        }

        return collect($request->input('excluded_weekdays', []))
            ->map(fn($d) => (int) $d)
            ->filter(fn($d) => $d >= 0 && $d <= 6)
            ->unique()
            ->values();
    }

    /**
     * Merges all three exclusion sources (device outages, weekday pattern,
     * ad-hoc dates) into a single date set for query filtering / working-day
     * counting, while also splitting out which of those dates should render
     * as a visible calendar row ('outage' / 'excluded') versus be hidden
     * from the calendar entirely (the weekday pattern — same as the old
     * isWeekend() behavior, just configurable now).
     *
     * Returns:
     *   'all'       — every excluded date string, for whereNotIn() / working-day counts
     *   'visible'   — outages + ad-hoc dates, rendered as a labeled row
     *   'hidden'    — weekday-pattern dates, never rendered as a row at all
     *   'outages'   — persisted DeviceOutageDate rows only, to pick the row label
     *   'weekdays'  — the resolved excluded-weekday ints, for checkbox pre-check in the view
     */
    private function resolveExclusions(Request $request, string $dateFrom, string $dateTo): array
    {
        $deviceOutages    = $this->getOutageDates($dateFrom, $dateTo);
        $excludedWeekdays = $this->resolveExcludedWeekdays($request);

        $weekdayDates = collect();
        if ($excludedWeekdays->isNotEmpty()) {
            $cursor = Carbon::parse($dateFrom);
            $end    = Carbon::parse($dateTo);
            while ($cursor->lte($end)) {
                if ($excludedWeekdays->contains($cursor->dayOfWeek)) {
                    $weekdayDates->push($cursor->toDateString());
                }
                $cursor->addDay();
            }
        }

        $adHoc = collect($request->input('excluded_dates', []))
            ->filter()
            ->map(function ($d) {
                try {
                    return Carbon::parse($d)->toDateString();
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->filter()
            ->filter(fn($d) => $d >= $dateFrom && $d <= $dateTo);

        $visible = $deviceOutages->merge($adHoc)->unique()->values();
        $all     = $visible->merge($weekdayDates)->unique()->values();
        $hidden  = $weekdayDates->diff($visible)->values();

        return [
            'all'      => $all,
            'visible'  => $visible,
            'hidden'   => $hidden,
            'outages'  => $deviceOutages,
            'weekdays' => $excludedWeekdays,
        ];
    }

    private function getWorkingDays(string $dateFrom, string $dateTo, ?Collection $excludedDates = null): int
    {
        $excludedDates ??= collect();

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

    private function buildCalendarWithRecords(string $dateFrom, string $dateTo, $records, array $excl): array
    {
        $byDate = $records->keyBy(fn($r) => Carbon::parse($r->attendance_date)->toDateString());

        $days    = [];
        $current = Carbon::parse($dateFrom);
        $end     = Carbon::parse($dateTo);

        while ($current->lte($end) && $current->lte(now())) {
            $key = $current->toDateString();

            // Weekday-pattern exclusion (e.g. default Sat/Sun) — never shown as a row,
            // same as the old hardcoded isWeekend() skip.
            if ($excl['hidden']->contains($key)) {
                $current->addDay();
                continue;
            }

            if ($excl['visible']->contains($key)) {
                $days[] = [
                    'date'     => $key,
                    'label'    => $current->format('D, d M'),
                    // 'outage' = flagged device downtime, 'excluded' = admin ticked it
                    // in the report's Exclude Days panel for this view only.
                    'status'   => $excl['outages']->contains($key) ? 'outage' : 'excluded',
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