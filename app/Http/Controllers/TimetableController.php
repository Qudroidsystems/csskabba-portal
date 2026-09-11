<?php

namespace App\Http\Controllers;

use App\Mail\TimetableNotificationMail;
use App\Models\Holiday;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\Schoolclass;
use App\Models\SchoolInformation;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Subject;
use App\Models\Subjectclass;
use App\Models\SubjectTeacher;
use App\Models\SubjectRegistrationStatus;
use App\Models\SubstituteAssignment;
use App\Models\TeacherAvailability;
use App\Models\TimetableConstraint;
use App\Models\TimetableNotification;
use App\Models\TimetablePeriod;
use App\Models\TimetableReport;
use App\Models\TimetableSetting;
use App\Models\TimetableSlot;
use App\Models\User;
use App\Models\Schoolarm;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Barryvdh\DomPDF\Facade\Pdf;

class TimetableController extends Controller
{
    const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    const DAYS_MAP = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5];

    const DAY_COLORS = [
        'Monday'    => '#1565C0',
        'Tuesday'   => '#6A1B9A',
        'Wednesday' => '#1B5E20',
        'Thursday'  => '#E65100',
        'Friday'    => '#880E4F',
    ];

    const SUBJECT_PALETTE = [
        '#DBEAFE','#D1FAE5','#FEF3C7','#FCE7F3','#E0E7FF',
        '#DCFCE7','#FEE2E2','#EDE9FE','#F0F9FF','#FFF7ED',
    ];

    const EDITING_LOCK_TTL_MINUTES = 3;

    public function __construct()
    {
        $this->middleware('permission:View timetable|Create timetable|Edit timetable|Delete timetable|Generate timetable', ['only' => ['index', 'getSetting', 'getGrid', 'heartbeat', 'releaseEditing', 'getSavedTimetables']]);
        $this->middleware('permission:Create timetable', ['only' => ['setup', 'saveSettings']]);
        $this->middleware('permission:Edit timetable', ['only' => ['saveSlot', 'bulkUpdateSlots', 'cloneSetting', 'resolveConflict']]);
        $this->middleware('permission:Delete timetable', ['only' => ['deleteSetting']]);
        $this->middleware('permission:Generate timetable', ['only' => ['autoGenerate', 'autoGenerateWholeSchool', 'applyGenerationTemplate', 'getTeacherAssignments']]);
        $this->middleware('permission:View my timetable', ['only' => ['teacherView', 'exportTeacherTimetable']]);
        $this->middleware('permission:Manage timetable settings', ['only' => ['saveSettings', 'rebuildPeriodsFromAnchors', 'saveHalfDays', 'saveFreePeriods']]);
        $this->middleware('permission:Manage timetable constraints', ['only' => ['saveConstraints']]);
        $this->middleware('permission:View timetable reports', ['only' => ['workloadDashboard', 'generateAnalytics']]);
        $this->middleware('permission:Export timetable', ['only' => ['export', 'exportWholeSchool']]);
        $this->middleware('permission:Request substitute', ['only' => ['requestSubstitute']]);
        $this->middleware('permission:Approve substitute', ['only' => ['approveSubstitute']]);
        $this->middleware('permission:View substitute requests', ['only' => ['getSubstituteRequests']]);
        $this->middleware('permission:Manage teacher availability', ['only' => ['saveTeacherAvailability', 'getTeacherAvailability']]);
        $this->middleware('permission:Check timetable conflicts', ['only' => ['checkConflicts', 'checkConflictsScope']]);
        $this->middleware('permission:Send timetable notifications', ['only' => ['sendNotifications', 'publishAndNotify']]);
        $this->middleware('permission:Publish timetable', ['only' => ['publishSetting', 'unpublishSetting', 'publishAndNotify', 'publishAndSaveSnapshot']]);
    }

    // =========================================================================
    // HELPER: Format time — strips seconds "08:00:00" → "08:00"
    // =========================================================================
    private function formatTime(?string $time): string
    {
        if (!$time) return '';
        return substr($time, 0, 5);
    }

    // =========================================================================
    // HELPER: Resolve arm name from schoolclass
    // =========================================================================
    private function resolveArmName($schoolclass): string
    {
        if (!$schoolclass) return '';

        // Raw-joined lookups (the many `leftJoin('schoolarm', ...)->select([
        // ..., 'schoolarm.arm as arm_name'])` calls throughout this
        // controller) alias the arm's display name onto `arm_name` directly
        // — these never touch the real `arm` column or any Eloquent
        // relation at all, so check this first.
        if (!empty($schoolclass->arm_name)) return ' ' . $schoolclass->arm_name;

        // For genuine Schoolclass Eloquent models: the FK column AND the
        // relation method are BOTH named `arm` (see Schoolclass::arm()) —
        // and Eloquent always resolves a raw attribute before a same-named
        // relation, so `$schoolclass->arm` here is ALWAYS the raw numeric
        // FK, never the related Schoolarm model, no matter what was
        // eager-loaded under the name 'arm'. `armRelation()` is the
        // distinctly-named relation on this model that actually avoids the
        // collision — use that instead.
        $arm = $schoolclass->armRelation ?? null;
        if (is_object($arm) && isset($arm->arm)) return ' ' . $arm->arm;

        if (is_string($schoolclass->arm ?? null) && !is_numeric($schoolclass->arm)) return ' ' . $schoolclass->arm;

        if (is_numeric($schoolclass->arm ?? null)) {
            $armModel = Schoolarm::find($schoolclass->arm);
            if ($armModel?->arm) return ' ' . $armModel->arm;
        }

        return '';
    }

    private function getClassName($schoolclass): string
    {
        if (!$schoolclass) return 'Unknown Class';
        return ($schoolclass->schoolclass ?? '') . $this->resolveArmName($schoolclass);
    }

    // =========================================================================
    // HELPER: Check if two slots represent an intentional combined session
    // =========================================================================
    private function isCombinedSession($a, $b): bool
    {
        return $a && $b
            && $a->teacher_id && $b->teacher_id && $a->teacher_id == $b->teacher_id
            && $a->subject_id && $b->subject_id && $a->subject_id == $b->subject_id
            && $a->room_id && $b->room_id && $a->room_id == $b->room_id;
    }

    // =========================================================================
    // HELPER: Canonical day+clock-time signature for matching slots ACROSS
    // settings. Every TimetableSetting (i.e. every class/arm) owns its own
    // TimetablePeriod rows, so "period_id" is only meaningful *within* a
    // single setting — the same 09:20–10:00 slot has a different period_id
    // in every class's timetable. Any comparison that needs to span multiple
    // settings (conflict checks, auto-generate occupancy tracking, cloning,
    // the teacher's merged grid) MUST key on day + start_time + end_time
    // instead of period_id, or cross-arm clashes are silently invisible.
    // =========================================================================
    private function periodTimeSignature(?TimetablePeriod $period): ?string
    {
        if (!$period || !$period->start_time || !$period->end_time) return null;
        return substr($period->start_time, 0, 5) . '-' . substr($period->end_time, 0, 5);
    }

    // =========================================================================
    // HELPER: Base query for slots in OTHER settings occupying the same
    // day + clock-time as $period, within a session/term scope.
    // =========================================================================
    private function crossSettingSlotsAtTime(TimetablePeriod $period, string $day, int $sessionId, ?int $termId, ?int $excludeSettingId = null)
    {
        return TimetableSlot::query()
            ->join('timetable_periods', 'timetable_periods.id', '=', 'timetable_slots.period_id')
            ->where('timetable_slots.day', $day)
            ->whereRaw('substr(timetable_periods.start_time, 1, 5) = ?', [substr($period->start_time, 0, 5)])
            ->whereRaw('substr(timetable_periods.end_time, 1, 5) = ?', [substr($period->end_time, 0, 5)])
            ->where('timetable_slots.is_free', false)
            ->whereNotNull('timetable_slots.subject_id')
            ->when($excludeSettingId, fn($q) => $q->where('timetable_slots.setting_id', '!=', $excludeSettingId))
            ->whereHas('setting', function ($q) use ($sessionId, $termId) {
                $q->where('session_id', $sessionId)->where('is_active', true);
                if ($termId) $q->where('term_id', $termId);
                else         $q->whereNull('term_id');
            })
            ->select('timetable_slots.*');
    }

    // =========================================================================
    // HELPER: Human-readable suggestion text
    // =========================================================================
    private function buildSuggestionText(string $teacherName, string $className, array $alternatives): string
    {
        if (empty($alternatives)) {
            return "No free slots found for {$teacherName} in {$className}. Consider reviewing the schedule or assigning a substitute.";
        }
        $top  = $alternatives[0];
        $more = count($alternatives) > 1 ? ' (' . (count($alternatives) - 1) . ' more available)' : '';
        return "Suggested: Move to {$top['day']}, {$top['period_name']} ({$top['period_time']}){$more}.";
    }

    // =========================================================================
    // HELPER: Find alternative (free) slots for a teacher
    // =========================================================================
    private function findAlternativeSlots($teacherId, $currentPeriodId, $currentDay, $currentSetting): array
    {
        $alternatives = [];
        $settingId    = $currentSetting->id;
        $sessionId    = $currentSetting->session_id;
        $termId       = $currentSetting->term_id;
        $days         = $currentSetting->active_days ?? self::DAYS;

        $periods = TimetablePeriod::where('setting_id', $settingId)
            ->where('type', 'lesson')
            ->orderBy('order')
            ->get();

        // The teacher's busy CLOCK-TIME slots across every one of their
        // settings/classes — not just this one. Keyed by day+time, since
        // period_id from another setting can't be compared to this one.
        $teacherBusyKeys = TimetableSlot::whereHas('setting', function($q) use ($sessionId, $termId) {
                $q->where('session_id', $sessionId)->where('is_active', true);
                if ($termId) $q->where('term_id', $termId);
                else         $q->whereNull('term_id');
            })
            ->where('teacher_id', $teacherId)
            ->where('is_free', false)
            ->whereNotNull('subject_id')
            ->with('period:id,start_time,end_time')
            ->get(['day', 'period_id'])
            ->filter(fn($s) => $s->period)
            ->mapWithKeys(fn($s) => [$s->day . '|' . $this->periodTimeSignature($s->period) => true]);

        foreach ($days as $day) {
            foreach ($periods as $period) {
                if ($day === $currentDay && $period->id == $currentPeriodId) continue;
                if ($teacherBusyKeys->has($day . '|' . $this->periodTimeSignature($period))) continue;
                $slot       = TimetableSlot::where('setting_id', $settingId)
                    ->where('period_id', $period->id)->where('day', $day)->first();
                $isSlotFree = !$slot || $slot->is_free || !$slot->subject_id;
                if ($isSlotFree) {
                    $alternatives[] = [
                        'day'         => $day,
                        'period_id'   => $period->id,
                        'period_name' => $period->name,
                        'period_time' => $this->formatTime($period->start_time) . ' – ' . $this->formatTime($period->end_time),
                        'is_available'=> true,
                    ];
                    if (count($alternatives) >= 5) break 2;
                }
            }
        }
        return $alternatives;
    }

    // =========================================================================
    // HELPER: Find available rooms for a given period/day
    // =========================================================================
    private function findAlternativeRooms($excludeRoomId, $periodId, $day, $sessionId, $termId): array
    {
        $period = TimetablePeriod::find($periodId);
        if (!$period) return [];

        return Room::where('is_active', true)
            ->when($excludeRoomId, fn($q) => $q->where('id', '!=', $excludeRoomId))
            ->whereNotIn('id', function($q) use ($period, $day, $sessionId, $termId) {
                // Match by CLOCK TIME, not period_id — a room booked under a
                // different setting's period row for the same 09:20–10:00
                // slot would otherwise be missed entirely.
                $q->select('timetable_slots.room_id')
                  ->from('timetable_slots')
                  ->join('timetable_periods', 'timetable_periods.id', '=', 'timetable_slots.period_id')
                  ->where('timetable_slots.day', $day)
                  ->whereRaw('substr(timetable_periods.start_time, 1, 5) = ?', [substr($period->start_time, 0, 5)])
                  ->whereRaw('substr(timetable_periods.end_time, 1, 5) = ?', [substr($period->end_time, 0, 5)])
                  ->whereNotNull('timetable_slots.room_id')
                  ->where('timetable_slots.is_free', false)
                  ->whereIn('timetable_slots.setting_id', function($q2) use ($sessionId, $termId) {
                      $q2->select('id')->from('timetable_settings')
                         ->where('session_id', $sessionId)->where('is_active', true);
                      if ($termId) $q2->where('term_id', $termId);
                  });
            })
            ->limit(5)->get(['id', 'room_name', 'room_code', 'type', 'capacity'])
            ->map(fn($r) => [
                'id'       => $r->id,
                'label'    => $r->room_name
                            . ($r->room_code ? ' (' . $r->room_code . ')' : '')
                            . ($r->capacity  ? ' · ' . $r->capacity . ' seats' : ''),
                'type'     => $r->type,
                'capacity' => $r->capacity,
            ])->toArray();
    }

    // =========================================================================
    // HELPER: Reject an edit attempt against a published (locked) timetable
    // =========================================================================
    private function publishedLockResponse(TimetableSetting $setting): ?JsonResponse
    {
        if (!$setting->is_published) return null;

        return response()->json([
            'success'      => false,
            'is_locked'    => true,
            'message'      => 'This timetable is published and locked. Unpublish it first to make changes — teachers will need to be re-notified afterward.',
            'published_at' => optional($setting->published_at)->format('d M Y, H:i'),
            'published_by' => $setting->publisher?->name,
        ], 423);
    }

    // =========================================================================
    // HELPER: Reject a save when the record was changed by someone else since
    // the client last loaded it.
    // =========================================================================
    private function versionConflictResponse(TimetableSetting $setting, ?string $expectedUpdatedAt): ?JsonResponse
    {
        if (!$expectedUpdatedAt) return null;
        if ($setting->updated_at->eq(Carbon::parse($expectedUpdatedAt))) return null;

        return response()->json([
            'success'              => false,
            'has_version_conflict' => true,
            'message'              => 'This timetable was changed by ' . ($setting->updater?->name ?? 'someone else')
                . ' at ' . $setting->updated_at->format('d M Y, H:i:s') . '. Reload to see the latest version.',
            'current_updated_at'   => $setting->updated_at->toISOString(),
        ], 409);
    }

    // =========================================================================
    // HELPER: For destructive-but-non-mutating-source actions (clone), warn
    // if someone else appears to be actively editing the source right now.
    // =========================================================================
    private function editingRecentlyResponse(TimetableSetting $setting, string $actionMessage): ?JsonResponse
    {
        if (!$setting->editing_by || $setting->editing_by == Auth::id()) return null;
        if (!$setting->editing_at || $setting->editing_at->diffInMinutes(now()) > self::EDITING_LOCK_TTL_MINUTES) return null;

        return response()->json([
            'success'         => false,
            'is_being_edited' => true,
            'message'         => ($setting->editor?->name ?? 'Someone') . ' is currently editing this timetable (started '
                . $setting->editing_at->diffForHumans() . "). {$actionMessage}",
        ], 409);
    }

    // =========================================================================
    // HELPER: Look up the holiday (if any) covering a specific calendar date
    // =========================================================================
    private function getHolidayForDate(Carbon $date, ?int $sessionId, ?int $termId = null): ?Holiday
    {
        return Holiday::whereDate('date', $date->toDateString())
            ->where(function ($q) use ($sessionId) {
                $q->whereNull('session_id')->orWhere('session_id', $sessionId);
            })
            ->where(function ($q) use ($termId) {
                $q->whereNull('term_id')->orWhere('term_id', $termId);
            })
            ->orderByDesc('is_full_day')
            ->first();
    }

    // =========================================================================
    // ICS CALENDAR FEED — public, signed-URL, no session auth required.
    // =========================================================================
    public function exportIcs(int $teacherId)
    {
        $teacher = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->findOrFail($teacherId);

        $sessionId = Schoolsession::where('status', 'Current')->value('id')
            ?? Schoolsession::latest('id')->value('id');
        $session = Schoolsession::findOrFail($sessionId);

        $slots = TimetableSlot::where('teacher_id', $teacher->id)
            ->whereNotNull('subject_id')
            ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId)->where('is_active', true))
            ->with(['period', 'subject', 'setting.schoolclass', 'room'])
            ->get();

        $ics = $this->buildIcsFeed($teacher, $session, $slots);

        return response($ics, 200)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="timetable-' . $teacher->id . '.ics"')
            ->header('Cache-Control', 'no-cache');
    }

    // =========================================================================
    // PRIVATE: Build the raw ICS document for a teacher's weekly slots.
    // =========================================================================
    private function buildIcsFeed(User $teacher, Schoolsession $session, $slots): string
    {
        $tz        = config('app.timezone', 'UTC');
        $startDate = now()->startOfWeek(Carbon::MONDAY);
        $endDate   = $startDate->copy()->addMonths(18);

        $byDayCode = ['Monday'=>'MO','Tuesday'=>'TU','Wednesday'=>'WE','Thursday'=>'TH','Friday'=>'FR','Saturday'=>'SA','Sunday'=>'SU'];

        $holidays = Holiday::where('is_full_day', true)
            ->whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', $endDate->toDateString())
            ->where(fn($q) => $q->whereNull('session_id')->orWhere('session_id', $session->id))
            ->get();

        $lines = [
            'BEGIN:VCALENDAR', 'VERSION:2.0',
            'PRODID:-//' . config('app.name', 'School') . '//Timetable//EN',
            'CALSCALE:GREGORIAN', 'METHOD:PUBLISH',
            $this->icsFold('X-WR-CALNAME:' . $this->icsEscape(($teacher->name ?? 'My') . ' Timetable')),
            'X-WR-TIMEZONE:' . $tz,
            'REFRESH-INTERVAL;VALUE=DURATION:PT12H',
            'X-PUBLISHED-TTL:PT12H',
        ];

        foreach ($slots as $slot) {
            $day     = $slot->day;
            $dayCode = $byDayCode[$day] ?? null;
            if (!$dayCode || !$slot->period) continue;

            $first = $startDate->copy();
            while ($first->format('l') !== $day) $first->addDay();

            [$sh, $sm] = explode(':', substr($slot->period->start_time, 0, 5));
            [$eh, $em] = explode(':', substr($slot->period->end_time, 0, 5));

            $dtStart = $first->copy()->setTime((int) $sh, (int) $sm);
            $dtEnd   = $first->copy()->setTime((int) $eh, (int) $em);

            $className = $this->getClassName($slot->setting?->schoolclass);
            $roomName  = $slot->room?->room_name;

            $descParts = array_filter([
                $className ? "Class: {$className}" : null,
                $roomName  ? "Room: {$roomName}"    : null,
                $slot->is_double ? 'Double period'  : null,
            ]);

            $exdates = [];
            foreach ($holidays as $h) {
                $hDate = Carbon::parse($h->date);
                if ($hDate->format('l') === $day && $hDate->gte($first)) {
                    $exdates[] = $hDate->copy()->setTime((int) $sh, (int) $sm)->format('Ymd\THis');
                }
            }

            $uid = 'slot-' . $slot->id . '-' . $session->id . '@' . parse_url(config('app.url'), PHP_URL_HOST);

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $uid;
            $lines[] = 'DTSTAMP:' . now()->utc()->format('Ymd\THis\Z');
            $lines[] = 'DTSTART;TZID=' . $tz . ':' . $dtStart->format('Ymd\THis');
            $lines[] = 'DTEND;TZID=' . $tz . ':' . $dtEnd->format('Ymd\THis');
            $lines[] = $this->icsFold('RRULE:FREQ=WEEKLY;BYDAY=' . $dayCode . ';UNTIL=' . $endDate->copy()->utc()->format('Ymd\THis\Z'));
            if ($exdates) $lines[] = $this->icsFold('EXDATE;TZID=' . $tz . ':' . implode(',', $exdates));
            $lines[] = $this->icsFold('SUMMARY:' . $this->icsEscape(($slot->subject?->subject ?? 'Class') . ' — ' . $className));
            if ($descParts) $lines[] = $this->icsFold('DESCRIPTION:' . $this->icsEscape(implode('\n', $descParts)));
            if ($roomName)  $lines[] = $this->icsFold('LOCATION:' . $this->icsEscape($roomName));
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';
        return implode("\r\n", $lines) . "\r\n";
    }

    private function icsEscape(string $text): string
    {
        return str_replace(["\\", ",", ";", "\n"], ["\\\\", "\\,", "\\;", "\\n"], $text);
    }

    private function icsFold(string $line): string
    {
        $folded = '';
        while (mb_strlen($line, '8bit') > 75) {
            $folded .= mb_strcut($line, 0, 75, '8bit') . "\r\n ";
            $line = mb_strcut($line, 75, null, '8bit');
        }
        return $folded . $line;
    }

    // =========================================================================
    // HELPER: For every (day, period) pair, resolve what it actually IS that day.
    // =========================================================================
    private function computeDayPeriodMeta(TimetableSetting $setting): array
    {
        $days        = $setting->active_days ?? self::DAYS;
        $periods     = $setting->periods->sortBy('order')->values();
        $halfDays    = $setting->half_days ?? [];
        $assemblyDay = $setting->assembly_day;

        $meta = [];
        foreach ($days as $day) {
            $teachingIndex = 0;
            $cutoff        = $halfDays[$day] ?? null;

            foreach ($periods as $period) {
                $effectiveType = $period->type;
                if ($period->type === 'assembly') {
                    $effectiveType = ($day === $assemblyDay) ? 'assembly' : 'lesson';
                }

                $applicable = true;
                if ($effectiveType === 'lesson') {
                    $teachingIndex++;
                    if ($cutoff && $teachingIndex > $cutoff) $applicable = false;
                }

                $meta[$day][$period->id] = ['applicable' => $applicable, 'effective_type' => $effectiveType];
            }
        }
        return $meta;
    }

    // =========================================================================
    // HELPER: Is this period immediately before/after a break or assembly?
    // =========================================================================
    private function isBreakAdjacent(TimetablePeriod $period, $allPeriods): bool
    {
        $ordered = $allPeriods->sortBy('order')->values();
        $idx     = $ordered->search(fn($p) => $p->id === $period->id);
        if ($idx === false) return false;

        $isNonLesson = fn($p) => $p && in_array($p->type, ['short_break', 'long_break', 'assembly']);
        return $isNonLesson($ordered->get($idx - 1)) || $isNonLesson($ordered->get($idx + 1));
    }

    // =========================================================================
    // HELPER: Turn a generation-wizard template into a periods[] array.
    // =========================================================================
    private function buildPeriodsFromTemplate(array $template): array
    {
        $periods = [];
        $lessonCount     = (int) $template['lessons_per_day'];
        $shortBreakAfter = $template['short_break_after'] ?? null;
        $longBreakAfter  = $template['long_break_after']  ?? null;
        $hasAssembly     = !empty($template['assembly_first_period']);

        $lessonNumber = 0;
        if ($hasAssembly) {
            $periods[] = ['name' => 'Assembly', 'type' => 'assembly'];
        }
        for ($i = 1; $i <= $lessonCount; $i++) {
            $lessonNumber++;
            $periods[] = ['name' => "Period {$lessonNumber}", 'type' => 'lesson'];
            if ($shortBreakAfter && $i == $shortBreakAfter) {
                $periods[] = ['name' => 'Short Break', 'type' => 'short_break'];
            }
            if ($longBreakAfter && $i == $longBreakAfter) {
                $periods[] = ['name' => 'Long Break', 'type' => 'long_break'];
            }
        }
        return $periods;
    }

    // =========================================================================
    // INDEX
    // =========================================================================
    public function index()
    {
        $pagetitle = 'Timetable Management';

        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
            ->orderBy('schoolclass.schoolclass')->orderBy('schoolarm.arm')->get();

        $schoolsessions = Schoolsession::orderByDesc('id')->get();
        $schoolterms    = Schoolterm::all();

        $subjectsWithTeachers = SubjectTeacher::with(['subject', 'staff'])->get()
            ->map(fn($st) => [
                'subject_id'   => $st->subjectid,
                'subject_name' => $st->subject->subject ?? 'Unknown',
                'teacher_id'   => $st->staffid,
                'teacher_name' => $st->staff->name ?? 'Unknown',
            ]);

        $settings = TimetableSetting::with(['session', 'term', 'creator', 'updater'])
            ->join('schoolclass', 'schoolclass.id', '=', 'timetable_settings.schoolclass_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select([
                'timetable_settings.*',
                'schoolclass.schoolclass as _class_name',
                'schoolarm.arm as _arm_name',
            ])
            ->where('timetable_settings.is_active', true)
            ->orderByDesc('timetable_settings.updated_at')
            ->get()
            ->each(function ($s) {
                $s->resolved_class_name = trim(($s->_class_name ?? '') . ' ' . ($s->_arm_name ?? ''));
            });

        return view('timetable.index', compact(
            'pagetitle', 'schoolclasses', 'schoolsessions', 'schoolterms', 'settings', 'subjectsWithTeachers'
        ));
    }

    // =========================================================================
    // EDITING PRESENCE
    // =========================================================================
    public function heartbeat(int $settingId): JsonResponse
    {
        DB::table('timetable_settings')->where('id', $settingId)->update([
            'editing_by' => Auth::id(),
            'editing_at' => now(),
        ]);
        return response()->json(['success' => true]);
    }

    public function releaseEditing(int $settingId): JsonResponse
    {
        DB::table('timetable_settings')->where('id', $settingId)
            ->where('editing_by', Auth::id())
            ->update(['editing_by' => null, 'editing_at' => null]);
        return response()->json(['success' => true]);
    }

    // =========================================================================
    // GET SETTING
    // =========================================================================
    public function getSetting(int $settingId): JsonResponse
    {
        $setting = TimetableSetting::with(['periods', 'constraints.subject', 'session', 'term', 'editor'])->findOrFail($settingId);

        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
            ->where('schoolclass.id', $setting->schoolclass_id)->first();
        $setting->setRelation('schoolclass', $schoolclass);

        $editingInfo = null;
        if ($setting->editing_by && $setting->editing_by != Auth::id() && $setting->editing_at
            && $setting->editing_at->diffInMinutes(now()) <= self::EDITING_LOCK_TTL_MINUTES) {
            $editingInfo = [
                'user_name' => $setting->editor?->name ?? 'Another user',
                'since'     => $setting->editing_at->diffForHumans(),
            ];
        }

        DB::table('timetable_settings')->where('id', $setting->id)->update([
            'editing_by' => Auth::id(),
            'editing_at' => now(),
        ]);

        $availableSubjects = SubjectTeacher::where('sessionid', $setting->session_id)
            ->when($setting->term_id, fn($q) => $q->where('termid', $setting->term_id))
            ->whereHas('subjectclass', fn($q) => $q->where('schoolclassid', $setting->schoolclass_id))
            ->with(['subject', 'staff'])
            ->get()
            ->map(fn($st) => [
                'subject_id'   => $st->subjectid,
                'subject_name' => $st->subject->subject ?? 'Unknown',
                'subject_code' => $st->subject->subject_code ?? '',
                'teacher_id'   => $st->staffid,
                'teacher_name' => $st->staff->name ?? 'Unknown',
                'term_name'    => $setting->term?->term ?? 'All Terms',
            ]);

        return response()->json([
            'success'            => true,
            'setting'            => $setting,
            'available_subjects' => $availableSubjects,
            'editing_info'       => $editingInfo,
        ]);
    }

    // =========================================================================
    // GET GRID
    // =========================================================================
    public function getGrid(int $settingId): JsonResponse
    {
        $setting = TimetableSetting::with(['periods', 'session', 'term'])->findOrFail($settingId);

        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
            ->where('schoolclass.id', $setting->schoolclass_id)->first();
        $setting->setRelation('schoolclass', $schoolclass);

        $slots = TimetableSlot::where('setting_id', $settingId)
            ->with(['subject', 'teacher', 'teacher.staffPicture', 'period', 'room'])->get();

        $grid = [];
        foreach ($slots as $slot) {
            $teacherPicture = null;
            if ($slot->teacher && $slot->teacher->staffPicture) {
                $teacherPicture = asset('storage/staff_avatars/' . $slot->teacher->staffPicture->picture);
            }
            $grid[$slot->period_id][$slot->day] = [
                'id'              => $slot->id,
                'subject_id'      => $slot->subject_id,
                'subject'         => $slot->subject?->subject,
                'subject_code'    => $slot->subject?->subject_code,
                'teacher_id'      => $slot->teacher_id,
                'teacher'         => $slot->teacher?->name,
                'teacher_picture' => $teacherPicture,
                'teacher_email'   => $slot->teacher?->email,
                'room_id'         => $slot->room_id,
                'room_name'       => $slot->room_id ? ($slot->room?->room_name ?? '') : '',
                'room_code'       => $slot->room_id ? ($slot->room?->room_code ?? '') : '',
                'is_double'       => $slot->is_double,
                'is_free'         => $slot->is_free,
                'notes'           => $slot->notes,
            ];
        }

        $allTeachers = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))
            ->with('staffPicture')->get()
            ->map(fn($t) => [
                'id'      => $t->id,
                'name'    => $t->name,
                'email'   => $t->email,
                'picture' => $t->staffPicture
                    ? asset('storage/staff_avatars/' . $t->staffPicture->picture)
                    : asset('storage/staff_avatars/default.png'),
            ]);

        $rooms = Room::where('is_active', true)->orderBy('room_name')
            ->get(['id', 'room_code', 'room_name', 'type', 'capacity'])
            ->map(fn($r) => [
                'id'       => $r->id,
                'value'    => $r->id,
                'name'     => $r->room_name,
                'code'     => $r->room_code,
                'type'     => $r->type,
                'capacity' => $r->capacity,
                'label'    => trim($r->room_name
                    . ($r->room_code ? ' (' . $r->room_code . ')' : '')
                    . ($r->capacity  ? ' · ' . $r->capacity . ' seats' : '')),
            ]);

        return response()->json([
            'success'         => true,
            'setting'         => $setting,
            'periods'         => $setting->periods,
            'grid'            => $grid,
            'days'            => $setting->active_days ?? self::DAYS,
            'teachers'        => $allTeachers,
            'rooms'           => $rooms,
            'class_name'      => $this->getClassName($setting->schoolclass),
            'day_period_meta' => $this->computeDayPeriodMeta($setting),
        ]);
    }

    // =========================================================================
    // SETUP
    // =========================================================================
    public function setup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schoolclass_id' => 'required|exists:schoolclass,id',
            'session_id'     => 'required|exists:schoolsession,id',
            'term_id'        => 'nullable|exists:schoolterm,id',
        ]);

        $setting = TimetableSetting::firstOrCreate(
            array_filter($validated),
            [
                'school_day_start'             => '08:00',
                'school_day_end'               => '14:30',
                'period_duration_minutes'      => 40,
                'short_break_duration_minutes' => 20,
                'long_break_duration_minutes'  => 40,
                'is_active'                    => true,
                'active_days'                  => self::DAYS,
                'created_by'                   => Auth::id(),
                'updated_by'                   => Auth::id(),
            ]
        );

        return response()->json(['success' => true, 'setting_id' => $setting->id, 'setting' => $setting]);
    }

    // =========================================================================
    // SAVE SETTINGS
    // =========================================================================
    public function saveSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id'                   => 'required|exists:timetable_settings,id',
            'expected_updated_at'          => 'nullable|date',
            'school_day_start'             => 'required|date_format:H:i',
            'school_day_end'               => 'required|date_format:H:i',
            'period_duration_minutes'      => 'required|integer|min:20|max:90',
            'short_break_duration_minutes' => 'required|integer|min:5|max:60',
            'long_break_duration_minutes'  => 'required|integer|min:10|max:90',
            'active_days'                  => 'required|array|min:1',
            'periods'                      => 'required|array|min:1',
            'periods.*.name'               => 'required|string|max:60',
            'periods.*.type'               => 'required|in:lesson,short_break,long_break,assembly,free',
        ]);

        try {
            DB::beginTransaction();
            $setting = TimetableSetting::findOrFail($validated['setting_id']);
            if ($lock = $this->publishedLockResponse($setting)) { DB::rollBack(); return $lock; }
            if ($conflict = $this->versionConflictResponse($setting, $validated['expected_updated_at'] ?? null)) { DB::rollBack(); return $conflict; }

            $isNew = $setting->periods()->count() === 0;

            $setting->update([
                'school_day_start'             => $validated['school_day_start'],
                'school_day_end'               => $validated['school_day_end'],
                'period_duration_minutes'      => $validated['period_duration_minutes'],
                'short_break_duration_minutes' => $validated['short_break_duration_minutes'],
                'long_break_duration_minutes'  => $validated['long_break_duration_minutes'],
                'active_days'                  => $validated['active_days'],
                'updated_by'                   => Auth::id(),
            ]);

            if ($isNew && !$setting->created_by) {
                $setting->created_by = Auth::id();
                $setting->saveQuietly();
            }

            TimetablePeriod::where('setting_id', $setting->id)->delete();

            $start = Carbon::createFromFormat('H:i', $validated['school_day_start']);
            $order = 0;

            foreach ($validated['periods'] as $p) {
                $order++;
                $duration = match($p['type']) {
                    'short_break' => $validated['short_break_duration_minutes'],
                    'long_break'  => $validated['long_break_duration_minutes'],
                    default       => $validated['period_duration_minutes'],
                };
                $end = (clone $start)->addMinutes($duration);
                TimetablePeriod::create([
                    'setting_id'       => $setting->id,
                    'order'            => $order,
                    'name'             => $p['name'],
                    'type'             => $p['type'],
                    'start_time'       => $start->format('H:i'),
                    'end_time'         => $end->format('H:i'),
                    'duration_minutes' => $duration,
                    'is_break'         => in_array($p['type'], ['short_break', 'long_break', 'assembly']),
                ]);
                $start = $end;
            }

            DB::commit();
            $this->logTimetableChange(Auth::id(), 'update', 'TimetableSetting', $setting->id, null, $setting->fresh()->toArray());
            return response()->json(['success' => true, 'setting' => $setting->load('periods')]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('saveSettings failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // REBUILD PERIODS FROM ANCHORS
    // =========================================================================
    public function rebuildPeriodsFromAnchors(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id'                    => 'required|exists:timetable_settings,id',
            'lessons_per_day'               => 'required|integer|min:1|max:12',
            'short_break_after_period'      => 'nullable|integer|min:1',
            'long_break_after_period'       => 'nullable|integer|min:1',
            'assembly_day'                  => 'nullable|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'short_break_duration_minutes'  => 'required|integer|min:5|max:60',
            'long_break_duration_minutes'   => 'required|integer|min:10|max:90',
            'period_duration_minutes'       => 'required|integer|min:20|max:90',
            'school_day_start'              => 'required|date_format:H:i',
            'max_lessons_per_day'           => 'nullable|integer|min:1',
            'free_periods_per_week'         => 'nullable|integer|min:0',
            'deprioritize_break_adjacent'   => 'boolean',
        ]);

        $setting = TimetableSetting::findOrFail($validated['setting_id']);
        if ($lock = $this->publishedLockResponse($setting)) return $lock;

        $setting->update([
            'lessons_per_day'              => $validated['lessons_per_day'],
            'short_break_after_period'     => $validated['short_break_after_period'] ?? null,
            'long_break_after_period'      => $validated['long_break_after_period'] ?? null,
            'assembly_day'                 => $validated['assembly_day'] ?? null,
            'period_duration_minutes'      => $validated['period_duration_minutes'],
            'short_break_duration_minutes' => $validated['short_break_duration_minutes'],
            'long_break_duration_minutes'  => $validated['long_break_duration_minutes'],
            'school_day_start'             => $validated['school_day_start'],
            'max_lessons_per_day'          => $validated['max_lessons_per_day'] ?? null,
            'free_periods_per_week'        => $validated['free_periods_per_week'] ?? 0,
            'deprioritize_break_adjacent'  => $validated['deprioritize_break_adjacent'] ?? true,
            'updated_by'                   => Auth::id(),
        ]);

        $periods = $this->buildPeriodsFromTemplate([
            'lessons_per_day'       => $validated['lessons_per_day'],
            'short_break_after'     => $validated['short_break_after_period'] ?? null,
            'long_break_after'      => $validated['long_break_after_period'] ?? null,
            'assembly_first_period' => !empty($validated['assembly_day']),
        ]);

        TimetablePeriod::where('setting_id', $setting->id)->delete();
        $start = Carbon::createFromFormat('H:i', $validated['school_day_start']);
        $order = 0;
        foreach ($periods as $p) {
            $order++;
            $duration = match ($p['type']) {
                'short_break' => $validated['short_break_duration_minutes'],
                'long_break'  => $validated['long_break_duration_minutes'],
                default       => $validated['period_duration_minutes'],
            };
            $end = (clone $start)->addMinutes($duration);
            TimetablePeriod::create([
                'setting_id' => $setting->id, 'order' => $order,
                'name' => $p['name'], 'type' => $p['type'],
                'start_time' => $start->format('H:i'), 'end_time' => $end->format('H:i'),
                'duration_minutes' => $duration,
                'is_break' => in_array($p['type'], ['short_break', 'long_break', 'assembly']),
            ]);
            $start = $end;
        }

        return response()->json(['success' => true, 'setting' => $setting->fresh(), 'periods' => $setting->periods()->get()]);
    }

    // =========================================================================
    // HALF-DAYS
    // =========================================================================
    public function saveHalfDays(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id'          => 'required|exists:timetable_settings,id',
            'half_days'           => 'nullable|array',
            'half_days.*.day'     => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'half_days.*.lessons' => 'required|integer|min:1',
        ]);

        $setting = TimetableSetting::findOrFail($validated['setting_id']);
        if ($lock = $this->publishedLockResponse($setting)) return $lock;

        $map = collect($validated['half_days'] ?? [])->mapWithKeys(fn($h) => [$h['day'] => $h['lessons']])->toArray();
        $setting->update(['half_days' => $map ?: null, 'updated_by' => Auth::id()]);

        return response()->json(['success' => true, 'half_days' => $setting->fresh()->half_days]);
    }

    // =========================================================================
    // FREE PERIODS — admin picks exactly WHICH day/period slots are forced
    // free for this specific class/arm, plus the target weekly count.
    // Forced slots are excluded from placement entirely during generation
    // (see runAutoGenerateCore's $forcedFreeKeys) — they are not just a
    // count the generator happens to land on, they are guaranteed free.
    // =========================================================================
    public function saveFreePeriods(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id'                        => 'required|exists:timetable_settings,id',
            'free_periods_per_week'             => 'nullable|integer|min:0|max:20',
            'preferred_free_slots'              => 'nullable|array',
            'preferred_free_slots.*.day'        => 'required_with:preferred_free_slots|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'preferred_free_slots.*.period_id'  => 'required_with:preferred_free_slots|exists:timetable_periods,id',
        ]);

        $setting = TimetableSetting::findOrFail($validated['setting_id']);
        if ($lock = $this->publishedLockResponse($setting)) return $lock;

        // Guard: every chosen period must actually belong to THIS setting —
        // period_id is only meaningful within the setting that owns the row
        // (see periodTimeSignature()'s note elsewhere in this file), so a
        // period_id from another class's timetable must be rejected here
        // rather than silently accepted and never matching anything.
        $ownPeriodIds = TimetablePeriod::where('setting_id', $setting->id)->pluck('id')->all();
        $slots = collect($validated['preferred_free_slots'] ?? [])
            ->filter(fn($s) => in_array((int) $s['period_id'], $ownPeriodIds, true))
            ->map(fn($s) => ['day' => $s['day'], 'period_id' => (int) $s['period_id']])
            ->unique(fn($s) => $s['day'] . '_' . $s['period_id'])
            ->values()->all();

        $setting->update([
            'preferred_free_slots'  => $slots ?: null,
            'free_periods_per_week' => $validated['free_periods_per_week'] ?? $setting->free_periods_per_week,
            'updated_by'            => Auth::id(),
        ]);

        return response()->json([
            'success'                => true,
            'preferred_free_slots'   => $setting->fresh()->preferred_free_slots,
            'free_periods_per_week'  => $setting->free_periods_per_week,
        ]);
    }

    // =========================================================================
    // APPLY GENERATION TEMPLATE
    // =========================================================================
    public function applyGenerationTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id'              => 'required|exists:schoolsession,id',
            'term_id'                 => 'nullable|exists:schoolterm,id',
            'schoolclass_ids'         => 'nullable|array',
            'schoolclass_ids.*'       => 'exists:schoolclass,id',
            'school_day_start'        => 'required|date_format:H:i',
            'school_day_end'          => 'required|date_format:H:i',
            'period_duration_minutes' => 'required|integer|min:20|max:90',
            'short_break_duration'    => 'required|integer|min:5|max:60',
            'long_break_duration'     => 'required|integer|min:10|max:90',
            'lessons_per_day'         => 'required|integer|min:1|max:12',
            'short_break_after'       => 'nullable|integer|min:1',
            'long_break_after'        => 'nullable|integer|min:1',
            'assembly_first_period'   => 'boolean',
            'assembly_day'            => 'nullable|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'active_days'             => 'required|array|min:1',
            'free_periods_per_week'   => 'nullable|integer|min:0',
            'free_periods_map'        => 'nullable|array',
            'free_periods_map.*'      => 'integer|min:0|max:20',
            'max_lessons_per_day'     => 'nullable|integer|min:1',
            'half_days'               => 'nullable|array',
            'half_days.*.day'         => 'required_with:half_days|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'half_days.*.lessons'     => 'required_with:half_days|integer|min:1',
            'deprioritize_break_adjacent' => 'boolean',
        ]);

        // When the wizard doesn't specify particular classes, "apply to
        // everything" must mean literally every school class — not just
        // the ones that already happen to have a SubjectTeacher row for
        // this session. The old fallback derived scope from SubjectTeacher,
        // which silently limited "whole school" generation to however many
        // classes already had subject/teacher assignments configured (this
        // is why only 8 of the school's classes were ever showing up).
        // A class with no subjects/teachers assigned yet still gets a
        // timetable shell here (all periods free) so the admin can see it
        // exists and fill it in later — it's simply not left out.
        $classIds = $validated['schoolclass_ids'] ?? Schoolclass::pluck('id')->toArray();

        if (empty($classIds)) {
            return response()->json(['success' => false, 'message' => 'No classes found for this scope.'], 404);
        }

        $periodTemplate = $this->buildPeriodsFromTemplate($validated);
        $halfDaysMap = isset($validated['half_days'])
            ? collect($validated['half_days'])->mapWithKeys(fn($h) => [$h['day'] => $h['lessons']])->toArray()
            : null;
        $results = [];

        DB::beginTransaction();
        try {
            foreach ($classIds as $classId) {
                $setting = TimetableSetting::firstOrCreate(
                    [
                        'schoolclass_id' => $classId,
                        'session_id'     => $validated['session_id'],
                        'term_id'        => $validated['term_id'] ?? null,
                    ],
                    ['is_active' => true, 'created_by' => Auth::id(), 'updated_by' => Auth::id()]
                );

                if ($lock = $this->publishedLockResponse($setting)) {
                    $results[] = ['schoolclass_id' => $classId, 'skipped' => true, 'reason' => 'published/locked'];
                    continue;
                }

                $setting->update([
                    'school_day_start'             => $validated['school_day_start'],
                    'school_day_end'               => $validated['school_day_end'],
                    'period_duration_minutes'      => $validated['period_duration_minutes'],
                    'short_break_duration_minutes' => $validated['short_break_duration'],
                    'long_break_duration_minutes'  => $validated['long_break_duration'],
                    'active_days'                  => $validated['active_days'],
                    // Per-class-arm override wins over the wizard-wide value,
                    // so the admin can e.g. give SSS1 arm4 three free periods
                    // and SSS2 arm4 only one, from the same wizard run.
                    'free_periods_per_week'        => $validated['free_periods_map'][$classId]
                                                        ?? $validated['free_periods_per_week']
                                                        ?? null,
                    'max_lessons_per_day'          => $validated['max_lessons_per_day'] ?? null,
                    'lessons_per_day'              => $validated['lessons_per_day'],
                    'short_break_after_period'     => $validated['short_break_after'] ?? null,
                    'long_break_after_period'      => $validated['long_break_after'] ?? null,
                    'assembly_day'                 => $validated['assembly_day'] ?? null,
                    'half_days'                    => $halfDaysMap,
                    'deprioritize_break_adjacent'  => $validated['deprioritize_break_adjacent'] ?? true,
                    'updated_by'                   => Auth::id(),
                ]);

                TimetablePeriod::where('setting_id', $setting->id)->delete();

                $start = Carbon::createFromFormat('H:i', $validated['school_day_start']);
                $order = 0;
                foreach ($periodTemplate as $p) {
                    $order++;
                    $duration = match ($p['type']) {
                        'short_break' => $validated['short_break_duration'],
                        'long_break'  => $validated['long_break_duration'],
                        default       => $validated['period_duration_minutes'],
                    };
                    $end = (clone $start)->addMinutes($duration);
                    TimetablePeriod::create([
                        'setting_id' => $setting->id, 'order' => $order,
                        'name' => $p['name'], 'type' => $p['type'],
                        'start_time' => $start->format('H:i'), 'end_time' => $end->format('H:i'),
                        'duration_minutes' => $duration,
                        'is_break' => in_array($p['type'], ['short_break', 'long_break', 'assembly']),
                    ]);
                    $start = $end;
                }

                $results[] = ['schoolclass_id' => $classId, 'setting_id' => $setting->id, 'skipped' => false];
            }

            DB::commit();
            return response()->json(['success' => true, 'applied_to' => count($results), 'results' => $results]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // SAVE CONSTRAINTS
    // =========================================================================
    public function saveConstraints(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id'                                    => 'required|exists:timetable_settings,id',
            'expected_updated_at'                            => 'nullable|date',
            'constraints'                                    => 'required|array',
            'constraints.*.subject_id'                       => 'required|exists:subject,id',
            'constraints.*.periods_per_week'                 => 'required|integer|min:1|max:10',
            'constraints.*.allow_double'                      => 'boolean',
            'constraints.*.max_double'                        => 'integer|min:0|max:5',
            'constraints.*.preferred_days'                    => 'nullable|array',
            'constraints.*.avoid_days'                        => 'nullable|array',
            'constraints.*.preferred_periods'                 => 'nullable|array',
            'constraints.*.is_compulsory'                     => 'boolean',
            'constraints.*.avoid_consecutive_double_days'     => 'boolean',
        ]);

        $setting = TimetableSetting::findOrFail($validated['setting_id']);
        if ($lock = $this->publishedLockResponse($setting)) return $lock;
        if ($conflict = $this->versionConflictResponse($setting, $validated['expected_updated_at'] ?? null)) return $conflict;

        DB::transaction(function () use ($validated) {
            TimetableConstraint::where('setting_id', $validated['setting_id'])->delete();
            foreach ($validated['constraints'] as $c) {
                TimetableConstraint::create([
                    'setting_id'                     => $validated['setting_id'],
                    'subject_id'                     => $c['subject_id'],
                    'periods_per_week'               => $c['periods_per_week'],
                    'allow_double_period'            => $c['allow_double'] ?? false,
                    'max_double_periods_per_week'    => $c['max_double'] ?? 1,
                    'preferred_days'                 => $c['preferred_days'] ?? null,
                    'avoid_days'                      => $c['avoid_days'] ?? null,
                    'preferred_periods'               => $c['preferred_periods'] ?? null,
                    'is_compulsory'                   => $c['is_compulsory'] ?? true,
                    'avoid_consecutive_double_days'   => $c['avoid_consecutive_double_days'] ?? true,
                ]);
            }
        });

        $setting->touch();

        return response()->json(['success' => true, 'updated_at' => $setting->fresh()->updated_at->toISOString()]);
    }

    // =========================================================================
    // REAL-TIME SLOT CONFLICT CHECK
    // =========================================================================
    public function checkSlotConflict(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id' => 'required|exists:timetable_settings,id',
            'period_id'  => 'required|exists:timetable_periods,id',
            'day'        => 'required|string',
            'teacher_id' => 'nullable|integer',
            'room_id'    => 'nullable|integer',
            'subject_id' => 'nullable|integer',
        ]);

        $setting   = TimetableSetting::findOrFail($validated['setting_id']);
        $sessionId = $setting->session_id;
        $termId    = $setting->term_id;
        $period    = TimetablePeriod::find($validated['period_id']);

        $conflicts = [];
        $warnings  = [];

        if (!empty($validated['teacher_id'])) {
            $teacherConflict = $period
                ? $this->crossSettingSlotsAtTime($period, $validated['day'], $sessionId, $termId, $validated['setting_id'])
                    ->where('timetable_slots.teacher_id', $validated['teacher_id'])
                    ->with(['setting', 'subject'])
                    ->first()
                : null;

            if ($teacherConflict) {
                $sc = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                    ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
                    ->where('schoolclass.id', $teacherConflict->setting->schoolclass_id)->first();
                $teacherConflict->setting->setRelation('schoolclass', $sc);

                $isCombined = !empty($validated['room_id']) && !empty($validated['subject_id'])
                    && $teacherConflict->room_id
                    && (int) $validated['room_id'] === (int) $teacherConflict->room_id
                    && (int) $validated['subject_id'] === (int) $teacherConflict->subject_id;

                if ($isCombined) {
                    $warnings[] = [
                        'type'    => 'combined_session',
                        'icon'    => '👥',
                        'message' => "Combined session — same teacher, subject and room as "
                            . $this->getClassName($teacherConflict->setting->schoolclass) . " at this time. Not a clash.",
                    ];
                } else {
                    $teacher      = User::find($validated['teacher_id']);
                    $className    = $this->getClassName($teacherConflict->setting->schoolclass);
                    $alternatives = $this->findAlternativeSlots(
                        $validated['teacher_id'], $validated['period_id'], $validated['day'], $setting
                    );

                    $conflicts[] = [
                        'type'         => 'teacher',
                        'severity'     => 'error',
                        'icon'         => '👨‍🏫',
                        'message'      => ($teacher?->name ?? 'This teacher')
                            . " is already teaching {$teacherConflict->subject?->subject} in {$className} at this time.",
                        'detail'       => "{$validated['day']} · {$period?->name} · "
                            . $this->formatTime($period?->start_time ?? '') . ' – '
                            . $this->formatTime($period?->end_time ?? ''),
                        'alternatives' => $alternatives,
                    ];
                }
            }

            $dailyCount = TimetableSlot::where('teacher_id', $validated['teacher_id'])
                ->where('day', $validated['day'])
                ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId)->where('is_active', true))
                ->where('is_free', false)->whereNotNull('subject_id')->count();

            if ($dailyCount >= 4) {
                $teacher = $teacher ?? User::find($validated['teacher_id']);
                $warnings[] = [
                    'type'    => 'workload',
                    'icon'    => '⚠️',
                    'message' => ($teacher?->name ?? 'This teacher')
                        . " already has {$dailyCount} period(s) on {$validated['day']}. High workload.",
                ];
            }
        }

        if (!empty($validated['room_id'])) {
            $roomConflict = $period
                ? $this->crossSettingSlotsAtTime($period, $validated['day'], $sessionId, $termId, $validated['setting_id'])
                    ->where('timetable_slots.room_id', $validated['room_id'])
                    ->with(['setting', 'subject', 'teacher'])
                    ->first()
                : null;

            if ($roomConflict) {
                $sc = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                    ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
                    ->where('schoolclass.id', $roomConflict->setting->schoolclass_id)->first();
                $roomConflict->setting->setRelation('schoolclass', $sc);

                $isCombinedRoom = !empty($validated['teacher_id']) && !empty($validated['subject_id'])
                    && $roomConflict->teacher_id
                    && (int) $validated['teacher_id'] === (int) $roomConflict->teacher_id
                    && (int) $validated['subject_id'] === (int) $roomConflict->subject_id;

                if (!$isCombinedRoom) {
                    $room     = Room::find($validated['room_id']);
                    $altRooms = $this->findAlternativeRooms(
                        $validated['room_id'], $validated['period_id'],
                        $validated['day'], $sessionId, $termId
                    );

                    $conflicts[] = [
                        'type'             => 'room',
                        'severity'         => 'error',
                        'icon'             => '🏫',
                        'message'          => ($room?->room_name ?? 'This room')
                            . " is already used for {$roomConflict->subject?->subject}"
                            . " in " . $this->getClassName($roomConflict->setting->schoolclass)
                            . " (taught by " . ($roomConflict->teacher?->name ?? '—') . ") at this time.",
                        'alternative_rooms'=> $altRooms,
                    ];
                }
            }

            if ($period) {
                $bookingConflict = RoomBooking::where('room_id', $validated['room_id'])
                    ->where('day', $validated['day'])
                    ->where('status', 'confirmed')
                    ->where(function($q) use ($period) {
                        $q->where('start_time', '<', $period->end_time)
                          ->where('end_time', '>', $period->start_time);
                    })->first();

                if ($bookingConflict) {
                    $room = $room ?? Room::find($validated['room_id']);
                    $conflicts[] = [
                        'type'     => 'room_booking',
                        'severity' => 'warning',
                        'icon'     => '📅',
                        'message'  => ($room?->room_name ?? 'This room')
                            . " has an existing booking"
                            . ($bookingConflict->purpose ? " ({$bookingConflict->purpose})" : '')
                            . " on {$validated['day']} from "
                            . $this->formatTime($bookingConflict->start_time)
                            . " to " . $this->formatTime($bookingConflict->end_time) . ".",
                        'alternative_rooms' => $this->findAlternativeRooms(
                            $validated['room_id'], $validated['period_id'],
                            $validated['day'], $sessionId, $termId
                        ),
                    ];
                }
            }
        }

        return response()->json([
            'success'   => true,
            'conflicts' => $conflicts,
            'warnings'  => $warnings,
            'has_error' => count(array_filter($conflicts, fn($c) => $c['severity'] === 'error')) > 0,
        ]);
    }

    // =========================================================================
    // SAVE SLOT
    // =========================================================================
    public function saveSlot(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id'          => 'required|exists:timetable_settings,id',
            'expected_updated_at' => 'nullable|date',
            'period_id'           => 'required|exists:timetable_periods,id',
            'day'                 => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'subject_id'          => 'nullable|exists:subject,id',
            'teacher_id'          => 'nullable|exists:users,id',
            'room_id'             => 'nullable|exists:rooms,id',
            'is_double'           => 'boolean',
            'is_free'             => 'boolean',
            'notes'               => 'nullable|string|max:191',
            'force_save'          => 'boolean',
        ]);

        $currentSetting = TimetableSetting::findOrFail($validated['setting_id']);
        if ($lock = $this->publishedLockResponse($currentSetting)) return $lock;
        if ($conflict = $this->versionConflictResponse($currentSetting, $validated['expected_updated_at'] ?? null)) return $conflict;

        $sessionId = $currentSetting->session_id;
        $termId    = $currentSetting->term_id;
        $period    = TimetablePeriod::find($validated['period_id']);

        $currentSchoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
            ->where('schoolclass.id', $currentSetting->schoolclass_id)->first();
        $currentSetting->setRelation('schoolclass', $currentSchoolclass);
        $currentClassName = $this->getClassName($currentSetting->schoolclass);

        if (empty($validated['force_save'])) {

            if (!empty($validated['teacher_id']) && $period) {
                $conflict = $this->crossSettingSlotsAtTime($period, $validated['day'], $sessionId, $termId, $validated['setting_id'])
                    ->where('timetable_slots.teacher_id', $validated['teacher_id'])
                    ->with(['setting', 'subject', 'period', 'teacher'])
                    ->first();

                if ($conflict) {
                    $isCombined = !empty($validated['room_id']) && !empty($validated['subject_id'])
                        && $conflict->room_id
                        && (int) $validated['room_id'] === (int) $conflict->room_id
                        && (int) $validated['subject_id'] === (int) $conflict->subject_id;

                    if (!$isCombined) {
                        $conflictSC = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
                            ->where('schoolclass.id', $conflict->setting->schoolclass_id)->first();
                        $conflict->setting->setRelation('schoolclass', $conflictSC);

                        $alternatives      = $this->findAlternativeSlots(
                            $validated['teacher_id'], $validated['period_id'], $validated['day'], $currentSetting
                        );
                        $conflictClassName = $this->getClassName($conflict->setting?->schoolclass);
                        $teacherName       = $conflict->teacher?->name ?? 'This teacher';
                        $subjectName       = $conflict->subject?->subject ?? 'another subject';
                        $periodName        = $conflict->period?->name ?? '';
                        $periodTime        = $this->formatTime($conflict->period?->start_time ?? '')
                                           . ' – ' . $this->formatTime($conflict->period?->end_time ?? '');

                        return response()->json([
                            'success'               => false,
                            'has_conflict'          => true,
                            'conflict_type'         => 'teacher_double_booking',
                            'message'               => "{$teacherName} is already teaching {$subjectName} in {$conflictClassName} on {$conflict->day}, {$periodName} ({$periodTime}).",
                            'conflict_details'      => [
                                'teacher'           => $teacherName,
                                'conflicting_class' => $conflictClassName,
                                'current_class'     => $currentClassName,
                                'subject'           => $subjectName,
                                'day'               => $conflict->day,
                                'period'            => $periodName,
                                'time'              => $periodTime,
                            ],
                            'alternatives'          => $alternatives,
                            'resolution_suggestion' => $this->buildSuggestionText($teacherName, $currentClassName, $alternatives),
                            'can_override'          => true,
                        ], 409);
                    }
                }
            }

            if (!empty($validated['room_id']) && $period) {
                $roomSlotConflict = $this->crossSettingSlotsAtTime($period, $validated['day'], $sessionId, $termId, $validated['setting_id'])
                    ->where('timetable_slots.room_id', $validated['room_id'])
                    ->with(['setting', 'subject', 'period', 'teacher'])
                    ->first();

                if ($roomSlotConflict) {
                    $isCombinedRoom = !empty($validated['teacher_id']) && !empty($validated['subject_id'])
                        && $roomSlotConflict->teacher_id
                        && (int) $validated['teacher_id'] === (int) $roomSlotConflict->teacher_id
                        && (int) $validated['subject_id'] === (int) $roomSlotConflict->subject_id;

                    if (!$isCombinedRoom) {
                        $conflictSC = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
                            ->where('schoolclass.id', $roomSlotConflict->setting->schoolclass_id)->first();
                        $roomSlotConflict->setting->setRelation('schoolclass', $conflictSC);

                        $room              = Room::find($validated['room_id']);
                        $roomName          = $room?->room_name ?? 'This room';
                        $conflictClassName = $this->getClassName($roomSlotConflict->setting?->schoolclass);
                        $subjectName       = $roomSlotConflict->subject?->subject ?? '—';
                        $teacherName       = $roomSlotConflict->teacher?->name ?? '—';
                        $periodName        = $roomSlotConflict->period?->name ?? '—';
                        $periodTime        = $this->formatTime($roomSlotConflict->period?->start_time ?? '')
                                           . ' – ' . $this->formatTime($roomSlotConflict->period?->end_time ?? '');

                        $alternativeRooms = $this->findAlternativeRooms(
                            $validated['room_id'], $validated['period_id'],
                            $validated['day'], $sessionId, $termId
                        );

                        return response()->json([
                            'success'          => false,
                            'has_conflict'     => true,
                            'conflict_type'    => 'room_double_booking',
                            'message'          => "{$roomName} is already in use for {$subjectName} ({$conflictClassName}, taught by {$teacherName}) on {$validated['day']}, {$periodName} ({$periodTime}).",
                            'conflict_details' => [
                                'room'              => $roomName,
                                'conflicting_class' => $conflictClassName,
                                'subject'           => $subjectName,
                                'teacher'           => $teacherName,
                                'day'               => $validated['day'],
                                'period'            => $periodName,
                                'time'              => $periodTime,
                            ],
                            'alternative_rooms' => $alternativeRooms,
                            'can_override'      => true,
                        ], 409);
                    }
                }

                if ($period) {
                    $bookedConflict = RoomBooking::where('room_id', $validated['room_id'])
                        ->where('day', $validated['day'])
                        ->where('status', 'confirmed')
                        ->where(function($q) use ($period) {
                            $q->where('start_time', '<', $period->end_time)
                              ->where('end_time', '>', $period->start_time);
                        })->first();

                    if ($bookedConflict) {
                        $room = Room::find($validated['room_id']);
                        return response()->json([
                            'success'       => false,
                            'has_conflict'  => true,
                            'conflict_type' => 'room_booking_conflict',
                            'message'       => ($room?->room_name ?? 'This room')
                                . " has an existing booking"
                                . ($bookedConflict->purpose ? " ({$bookedConflict->purpose})" : '')
                                . " on {$validated['day']} ({$this->formatTime($period->start_time)} – {$this->formatTime($period->end_time)}).",
                            'can_override'  => true,
                        ], 409);
                    }
                }
            }
        }

        $slot = TimetableSlot::updateOrCreate(
            [
                'setting_id' => $validated['setting_id'],
                'period_id'  => $validated['period_id'],
                'day'        => $validated['day'],
            ],
            [
                'subject_id' => $validated['subject_id'] ?? null,
                'teacher_id' => $validated['teacher_id'] ?? null,
                'room_id'    => $validated['room_id'] ?? null,
                'notes'      => $validated['notes'] ?? null,
                'is_double'  => $validated['is_double'] ?? false,
                'is_free'    => empty($validated['subject_id']),
            ]
        );

        try { $this->logTimetableChange(Auth::id(), 'update', 'TimetableSlot', $slot->id); }
        catch (\Exception $e) { Log::warning('Audit log failed: ' . $e->getMessage()); }

        try {
            if ($slot->wasChanged('teacher_id') && $slot->teacher_id) {
                $this->scheduleNotification($slot->teacher_id, $slot->id, 'change_alert');
            }
        } catch (\Exception $e) { Log::warning('Notification failed: ' . $e->getMessage()); }

        $currentSetting->touch();

        return response()->json([
            'success'            => true,
            'slot'               => $slot->load(['subject', 'teacher', 'room']),
            'setting_updated_at' => $currentSetting->fresh()->updated_at->toISOString(),
        ]);
    }

    // =========================================================================
    // RESOLVE CONFLICT — applies a suggested fix directly from the
    // conflicts modal. Two modes:
    //   action=move        move the slot to a different day/period within
    //                       its own class's timetable (uses the 'day' /
    //                       'period_id' / 'alternatives[].day' / [].period_id
    //                       already returned by buildConflictReport()).
    //   action=change_room reassign the room, keeping day/period as-is
    //                       (uses 'alternative_rooms[].id' from a
    //                       room_conflict entry).
    // Re-validates against a NEW clash at the destination before committing,
    // so picking a suggestion can never silently create a different
    // conflict in its place.
    // =========================================================================
    public function resolveConflict(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id'       => 'required|exists:timetable_settings,id',
            'day'              => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'period_id'        => 'required|exists:timetable_periods,id',
            'action'           => 'required|in:move,change_room',
            'target_day'       => 'required_if:action,move|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'target_period_id' => 'required_if:action,move|exists:timetable_periods,id',
            'target_room_id'   => 'required_if:action,change_room|exists:rooms,id',
        ]);

        $setting = TimetableSetting::findOrFail($validated['setting_id']);
        if ($lock = $this->publishedLockResponse($setting)) return $lock;

        $slot = TimetableSlot::where('setting_id', $setting->id)
            ->where('day', $validated['day'])
            ->where('period_id', $validated['period_id'])
            ->whereNotNull('subject_id')
            ->where('is_free', false)
            ->first();

        if (!$slot) {
            return response()->json([
                'success' => false,
                'message' => 'That slot could not be found — it may already have been moved or edited since this conflict was detected. Refresh and try again.',
            ], 404);
        }

        $sessionId = $setting->session_id;
        $termId    = $setting->term_id;

        if ($validated['action'] === 'move') {
            $targetPeriod = TimetablePeriod::where('id', $validated['target_period_id'])
                ->where('setting_id', $setting->id)->first();
            if (!$targetPeriod) {
                return response()->json(['success' => false, 'message' => 'That destination period does not belong to this class\'s timetable.'], 422);
            }

            $destinationOccupied = TimetableSlot::where('setting_id', $setting->id)
                ->where('day', $validated['target_day'])
                ->where('period_id', $validated['target_period_id'])
                ->whereNotNull('subject_id')
                ->where('is_free', false)
                ->exists();
            if ($destinationOccupied) {
                return response()->json(['success' => false, 'message' => 'That slot is already taken in this class\'s own timetable — pick a different alternative.'], 409);
            }

            if ($slot->teacher_id) {
                $clash = $this->crossSettingSlotsAtTime($targetPeriod, $validated['target_day'], $sessionId, $termId, $setting->id)
                    ->where('timetable_slots.teacher_id', $slot->teacher_id)->first();
                if ($clash && !$this->isCombinedSession($slot, $clash)) {
                    return response()->json(['success' => false, 'message' => 'Moving here would create a new teacher conflict elsewhere — pick a different alternative.'], 409);
                }
            }
            if ($slot->room_id) {
                $roomClash = $this->crossSettingSlotsAtTime($targetPeriod, $validated['target_day'], $sessionId, $termId, $setting->id)
                    ->where('timetable_slots.room_id', $slot->room_id)->first();
                if ($roomClash && !$this->isCombinedSession($slot, $roomClash)) {
                    return response()->json(['success' => false, 'message' => 'Moving here would create a new room conflict elsewhere — pick a different alternative.'], 409);
                }
            }

            $oldDay = $slot->day;
            $oldPeriodId = $slot->period_id;

            DB::transaction(function () use ($slot, $setting, $validated, $oldDay, $oldPeriodId) {
                $slot->update([
                    'day'       => $validated['target_day'],
                    'period_id' => $validated['target_period_id'],
                ]);

                // Leave the vacated slot as a genuine free period rather than
                // a hole with no row at all, so the class's own grid stays
                // fully populated and consistent with how auto-generate
                // fills gaps.
                TimetableSlot::updateOrCreate(
                    ['setting_id' => $setting->id, 'day' => $oldDay, 'period_id' => $oldPeriodId],
                    ['subject_id' => null, 'teacher_id' => null, 'room_id' => null, 'is_double' => false, 'is_free' => true, 'notes' => null]
                );
            });

            $message = "Moved to {$validated['target_day']}, {$targetPeriod->name}.";
        } else {
            $period = TimetablePeriod::find($validated['period_id']);
            $room   = Room::find($validated['target_room_id']);

            $roomClash = $this->crossSettingSlotsAtTime($period, $validated['day'], $sessionId, $termId, $setting->id)
                ->where('timetable_slots.room_id', $validated['target_room_id'])->first();
            if ($roomClash) {
                return response()->json(['success' => false, 'message' => 'That room is already taken at this time — pick a different alternative.'], 409);
            }

            $slot->update(['room_id' => $validated['target_room_id']]);
            $message = "Room changed to " . ($room?->room_name ?? 'the selected room') . ".";
        }

        try { $this->logTimetableChange(Auth::id(), 'update', 'TimetableSlot', $slot->id, null, ['conflict_resolved' => $message]); }
        catch (\Exception $e) { Log::warning('Audit log failed: ' . $e->getMessage()); }

        $setting->touch();

        return response()->json([
            'success' => true,
            'message' => $message,
            'slot'    => $slot->fresh()->load(['subject', 'teacher', 'room', 'period']),
        ]);
    }

    // =========================================================================
    // CHECK CONFLICTS — standalone entry point for per-class tab
    // =========================================================================
    public function checkConflicts(int $settingId): JsonResponse
    {
        $setting = TimetableSetting::findOrFail($settingId);
        return response()->json(
            $this->buildConflictReport((int) $setting->session_id, $setting->term_id)
        );
    }

    // =========================================================================
    // PRIVATE: Shared conflict-detection logic
    // =========================================================================
    private function buildConflictReport(int $sessionId, ?int $termId): array
    {
        $slots = TimetableSlot::whereHas('setting', function ($q) use ($sessionId, $termId) {
                $q->where('session_id', $sessionId)->where('is_active', true);
                if ($termId) $q->where('term_id', $termId);
                else         $q->whereNull('term_id');
            })
            ->whereNotNull('teacher_id')
            ->where('is_free', false)
            ->whereNotNull('subject_id')
            ->with(['period', 'subject', 'setting', 'teacher', 'teacher.staffPicture', 'room'])
            ->get();

        $classIds     = $slots->pluck('setting.schoolclass_id')->unique()->filter();
        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
            ->whereIn('schoolclass.id', $classIds)->get()->keyBy('id');

        foreach ($slots as $slot) {
            if ($slot->setting && isset($schoolclasses[$slot->setting->schoolclass_id])) {
                $slot->setting->setRelation('schoolclass', $schoolclasses[$slot->setting->schoolclass_id]);
            }
        }

        $conflicts = [];

        // Group by CLOCK TIME, not period_id — otherwise two different
        // classes/arms taught by the same teacher at the same time never
        // land in the same group, because each setting owns its own
        // TimetablePeriod rows (different period_id for the same time).
        $teacherGrouped = $slots->filter(fn($s) => $s->period)
            ->groupBy(fn($s) => $s->teacher_id . '|' . $s->day . '|' . $this->periodTimeSignature($s->period));

        foreach ($teacherGrouped as $group) {
            if ($group->count() < 2) continue;

            $first       = $group->first();
            $teacherName = $first->teacher?->name ?? '—';
            $periodName  = $first->period?->name ?? '—';
            $periodTime  = $this->formatTime($first->period?->start_time ?? '')
                         . ' – ' . $this->formatTime($first->period?->end_time ?? '');
            $classes     = $group->map(fn($s) => $this->getClassName($s->setting?->schoolclass))->unique()->values();
            $subjects    = $group->map(fn($s) => $s->subject?->subject ?? '—')->unique()->values();
            $classLevels = $group->map(fn($s) => $s->setting?->schoolclass?->schoolclass ?? '')->unique();
            $isCrossArm  = $classLevels->count() === 1 && $classes->count() > 1;

            $groupArr = $group->values();
            for ($i = 0; $i < $groupArr->count(); $i++) {
                for ($j = $i + 1; $j < $groupArr->count(); $j++) {
                    $a = $groupArr[$i];
                    $b = $groupArr[$j];

                    if ($this->isCombinedSession($a, $b)) continue;

                    $classA = $this->getClassName($a->setting?->schoolclass);
                    $classB = $this->getClassName($b->setting?->schoolclass);

                    // Alternatives are computed against slot A specifically
                    // (not just the group's first slot) so the "Resolve"
                    // button in the modal always moves the exact slot the
                    // suggestion was generated for.
                    $alternativesForA = $this->findAlternativeSlots(
                        $a->teacher_id, $a->period_id, $a->day, $a->setting
                    );

                    $conflicts[] = [
                        'type'                  => $isCrossArm ? 'cross_arm_conflict' : 'teacher_conflict',
                        'conflict_category'     => 'teacher',
                        'day'                   => $first->day,
                        'period'                => $periodName,
                        'period_time'           => $periodTime,
                        'period_id'             => $a->period_id,
                        'teacher'               => $teacherName,
                        'teacher_id'            => $first->teacher_id,
                        'teacher_picture'       => $first->teacher?->staffPicture
                            ? asset('storage/staff_avatars/' . $first->teacher->staffPicture->picture)
                            : null,
                        'subject_a'             => $a->subject?->subject ?? '—',
                        'subject_b'             => $b->subject?->subject ?? '—',
                        'class_a'               => $classA,
                        'class_b'               => $classB,
                        'is_cross_arm'          => $isCrossArm,
                        'setting_a_id'          => $a->setting_id,
                        'setting_b_id'          => $b->setting_id,
                        'all_classes'           => $classes,
                        'all_subjects'          => $subjects,
                        'alternatives'          => $alternativesForA,
                        'resolution_suggestion' => $this->buildSuggestionText($teacherName, $classA, $alternativesForA),
                    ];
                }
            }
        }

        $allSlotsWithRoom = TimetableSlot::whereHas('setting', function ($q) use ($sessionId, $termId) {
                $q->where('session_id', $sessionId)->where('is_active', true);
                if ($termId) $q->where('term_id', $termId);
                else         $q->whereNull('term_id');
            })
            ->whereNotNull('room_id')
            ->where('is_free', false)
            ->whereNotNull('subject_id')
            ->with(['period', 'subject', 'setting', 'teacher', 'room'])
            ->get();

        $roomClassIds = $allSlotsWithRoom->pluck('setting.schoolclass_id')->unique()->filter();
        $roomClasses  = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
            ->whereIn('schoolclass.id', $roomClassIds)->get()->keyBy('id');

        foreach ($allSlotsWithRoom as $slot) {
            if ($slot->setting && isset($roomClasses[$slot->setting->schoolclass_id])) {
                $slot->setting->setRelation('schoolclass', $roomClasses[$slot->setting->schoolclass_id]);
            }
        }

        // Same fix for rooms — group by CLOCK TIME, not period_id.
        $roomGrouped = $allSlotsWithRoom->filter(fn($s) => $s->period)
            ->groupBy(fn($s) => $s->room_id . '|' . $s->day . '|' . $this->periodTimeSignature($s->period));

        foreach ($roomGrouped as $group) {
            if ($group->count() < 2) continue;

            $first      = $group->first();
            $room       = $first->room;
            $periodName = $first->period?->name ?? '—';
            $periodTime = $this->formatTime($first->period?->start_time ?? '')
                        . ' – ' . $this->formatTime($first->period?->end_time ?? '');
            $classes    = $group->map(fn($s) => $this->getClassName($s->setting?->schoolclass))->unique()->values();
            $subjects   = $group->map(fn($s) => $s->subject?->subject ?? '—')->unique()->values();

            $groupArr = $group->values();
            for ($i = 0; $i < $groupArr->count(); $i++) {
                for ($j = $i + 1; $j < $groupArr->count(); $j++) {
                    $a = $groupArr[$i];
                    $b = $groupArr[$j];

                    if ($a->teacher_id && $b->teacher_id && $a->teacher_id == $b->teacher_id
                        && $a->subject_id && $b->subject_id && $a->subject_id == $b->subject_id) {
                        continue;
                    }

                    $classA = $this->getClassName($a->setting?->schoolclass);
                    $classB = $this->getClassName($b->setting?->schoolclass);

                    $altRoomsForA = $this->findAlternativeRooms(
                        $a->room_id, $a->period_id, $a->day, $sessionId, $termId
                    );

                    $conflicts[] = [
                        'type'                  => 'room_conflict',
                        'conflict_category'     => 'room',
                        'day'                   => $first->day,
                        'period'                => $periodName,
                        'period_time'           => $periodTime,
                        'period_id'             => $a->period_id,
                        'teacher'               => '🏫 ' . ($room?->room_name ?? 'Unknown Room'),
                        'teacher_id'            => null,
                        'teacher_picture'       => null,
                        'subject_a'             => $a->subject?->subject ?? '—',
                        'subject_b'             => $b->subject?->subject ?? '—',
                        'class_a'               => $classA,
                        'class_b'               => $classB,
                        'is_cross_arm'          => false,
                        'setting_a_id'          => $a->setting_id,
                        'setting_b_id'          => $b->setting_id,
                        'all_classes'           => $classes,
                        'all_subjects'          => $subjects,
                        'alternatives'          => [],
                        'alternative_rooms'     => $altRoomsForA,
                        'resolution_suggestion' => "Room conflict: {$classA} and {$classB} are both assigned to "
                            . ($room?->room_name ?? 'the same room')
                            . " on {$first->day}, {$periodName}. Assign one class to a different room.",
                    ];
                }
            }
        }

        return [
            'success'        => true,
            'conflicts'      => $conflicts,
            'conflict_count' => count($conflicts),
            'has_conflicts'  => count($conflicts) > 0,
            'checked_at'     => now()->format('d M Y, H:i:s'),
        ];
    }

    // =========================================================================
    // CHECK CONFLICTS — standalone entry point, no class context needed.
    // =========================================================================
    public function checkConflictsScope(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:schoolsession,id',
            'term_id'    => 'nullable|exists:schoolterm,id',
        ]);

        return response()->json(
            $this->buildConflictReport((int) $validated['session_id'], $validated['term_id'] ?? null)
        );
    }

    // =========================================================================
    // PRIVATE: Fast conflict count across a scope (for generation summary)
    // =========================================================================
    private function countConflictsForScope(int $sessionId, ?int $termId): array
    {
        $scopeFilter = function ($q) use ($sessionId, $termId) {
            $q->where('session_id', $sessionId)->where('is_active', true);
            if ($termId) $q->where('term_id', $termId);
            else $q->whereNull('term_id');
        };

        $teacherConflicts = TimetableSlot::whereHas('setting', $scopeFilter)
            ->whereNotNull('teacher_id')
            ->where('is_free', false)
            ->whereNotNull('subject_id')
            ->with('period:id,start_time,end_time')
            ->get(['id', 'teacher_id', 'day', 'period_id', 'subject_id', 'room_id'])
            ->filter(fn($s) => $s->period)
            ->groupBy(fn($s) => $s->teacher_id . '|' . $s->day . '|' . $this->periodTimeSignature($s->period))
            ->sum(function ($group) {
                $arr = $group->values();
                $count = 0;
                for ($i = 0; $i < $arr->count(); $i++) {
                    for ($j = $i + 1; $j < $arr->count(); $j++) {
                        if (!$this->isCombinedSession($arr[$i], $arr[$j])) $count++;
                    }
                }
                return $count;
            });

        $roomConflicts = TimetableSlot::whereHas('setting', $scopeFilter)
            ->whereNotNull('room_id')
            ->where('is_free', false)
            ->whereNotNull('subject_id')
            ->with('period:id,start_time,end_time')
            ->get(['id', 'room_id', 'day', 'period_id', 'teacher_id', 'subject_id'])
            ->filter(fn($s) => $s->period)
            ->groupBy(fn($s) => $s->room_id . '|' . $s->day . '|' . $this->periodTimeSignature($s->period))
            ->sum(function ($group) {
                $arr = $group->values();
                $count = 0;
                for ($i = 0; $i < $arr->count(); $i++) {
                    for ($j = $i + 1; $j < $arr->count(); $j++) {
                        $a = $arr[$i];
                        $b = $arr[$j];
                        $same = $a->teacher_id && $b->teacher_id && $a->teacher_id == $b->teacher_id
                            && $a->subject_id && $b->subject_id && $a->subject_id == $b->subject_id;
                        if (!$same) $count++;
                    }
                }
                return $count;
            });

        return [
            'teacher_conflicts' => $teacherConflicts,
            'room_conflicts'    => $roomConflicts,
            'total'             => $teacherConflicts + $roomConflicts,
        ];
    }

    // =========================================================================
    // AUTO-GENERATE (single class)
    // =========================================================================
    public function autoGenerate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id'          => 'required|exists:timetable_settings,id',
            'expected_updated_at' => 'nullable|date',
            'include_rooms'       => 'boolean',
            'seed'                => 'nullable|integer',
            'generation_name'     => 'nullable|string|max:150',
            'generation_notes'    => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $setting = TimetableSetting::with(['periods', 'constraints.subject'])->findOrFail($validated['setting_id']);
            if ($lock = $this->publishedLockResponse($setting)) { DB::rollBack(); return $lock; }
            if ($conflict = $this->versionConflictResponse($setting, $validated['expected_updated_at'] ?? null)) { DB::rollBack(); return $conflict; }

            TimetableSlot::where('setting_id', $setting->id)->delete();

            $includeRooms = $validated['include_rooms'] ?? false;

            // Explicit or freshly-rolled random seed — returned to the
            // caller so a result the admin likes can be reproduced later by
            // resending the same seed, even though generation is random by
            // default now (see runAutoGenerateCore's shuffle/jitter).
            $usedSeed = $validated['seed'] ?? random_int(1, 2147483647);
            mt_srand($usedSeed);

            // Cross-setting occupancy is tracked by CLOCK TIME (day + start +
            // end), never by period_id — period_id only has meaning within
            // the setting that owns the row, so keying by it here would let
            // the generator double-book a teacher across two class arms.
            $crossOccupied = [];
            TimetableSlot::whereHas('setting', function ($q) use ($setting) {
                    $q->where('session_id', $setting->session_id)->where('is_active', true)->where('id', '!=', $setting->id);
                    if ($setting->term_id) $q->where('term_id', $setting->term_id);
                    else                   $q->whereNull('term_id');
                })
                ->whereNotNull('teacher_id')->where('is_free', false)
                ->with('period:id,start_time,end_time')
                ->get(['teacher_id', 'period_id', 'day'])
                ->each(function ($occ) use (&$crossOccupied) {
                    if (!$occ->period) return;
                    $crossOccupied[$occ->teacher_id][$occ->day][] = $this->periodTimeSignature($occ->period);
                });

            $roomOccupied = [];
            if ($includeRooms) {
                TimetableSlot::whereHas('setting', function ($q) use ($setting) {
                        $q->where('session_id', $setting->session_id)->where('is_active', true)->where('id', '!=', $setting->id);
                        if ($setting->term_id) $q->where('term_id', $setting->term_id);
                        else                   $q->whereNull('term_id');
                    })
                    ->whereNotNull('room_id')->where('is_free', false)
                    ->with('period:id,start_time,end_time')
                    ->get(['room_id', 'period_id', 'day'])
                    ->each(function ($occ) use (&$roomOccupied) {
                        if (!$occ->period) return;
                        $roomOccupied[$occ->room_id][$occ->day][] = $this->periodTimeSignature($occ->period);
                    });
            }

            $stats = $this->runAutoGenerateCore($setting, $crossOccupied, $includeRooms, $roomOccupied);

            $setting->update([
                'generation_seed'  => $usedSeed,
                'generation_name'  => $validated['generation_name']  ?? $setting->generation_name,
                'generation_notes' => $validated['generation_notes'] ?? $setting->generation_notes,
                'generated_by'     => Auth::id(),
                'generated_at'     => now(),
            ]);

            mt_srand(); // reseed randomly so a persistent worker isn't stuck on this seed for unrelated requests

            DB::commit();
            return response()->json([
                'success'            => true,
                'message'            => 'Timetable generated successfully.',
                'stats'              => $stats,
                'used_seed'          => $usedSeed,
                'setting_updated_at' => $setting->fresh()->updated_at->toISOString(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            mt_srand();
            Log::error('autoGenerate failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // AUTO-GENERATE — WHOLE SCHOOL
    // =========================================================================
    public function autoGenerateWholeSchool(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id'        => 'required|exists:schoolsession,id',
            'term_id'           => 'nullable|exists:schoolterm,id',
            'schoolclass_ids'   => 'nullable|array',
            'schoolclass_ids.*' => 'exists:schoolclass,id',
            'force_unpublish'   => 'boolean',
            'include_rooms'     => 'boolean',
            'seed'              => 'nullable|integer',
            'generation_name'   => 'nullable|string|max:150',
            'generation_notes'  => 'nullable|string|max:1000',
        ]);

        $settings = TimetableSetting::with(['periods', 'constraints.subject', 'schoolclass'])
            ->where('session_id', $validated['session_id'])
            ->where('is_active', true)
            ->when($validated['term_id'] ?? null, fn($q) => $q->where('term_id', $validated['term_id']))
            ->when($validated['schoolclass_ids'] ?? null, fn($q) => $q->whereIn('schoolclass_id', $validated['schoolclass_ids']))
            ->get();

        if ($settings->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No timetables found for this scope. Create class timetables first (or run the Generation Wizard, which creates them automatically).'], 404);
        }

        $publishedLocked = $settings->filter(fn($s) => $s->is_published);
        if ($publishedLocked->isNotEmpty() && empty($validated['force_unpublish'])) {
            return response()->json([
                'success'          => false,
                'has_locked'       => true,
                'message'          => $publishedLocked->count() . ' of these timetables are published and locked.',
                'locked_classes'   => $publishedLocked->map(fn($s) => $this->getClassName($s->schoolclass))->values(),
            ], 423);
        }

        $ordered = $settings->shuffle()->sortByDesc(function ($s) {
            return $s->constraints->sum(fn($c) => ($c->is_compulsory ? 100 : 0) + $c->periods_per_week);
        })->values();

        $includeRooms = $validated['include_rooms'] ?? false;
        $results = [];

        try {
            DB::beginTransaction();

            TimetableSlot::whereIn('setting_id', $settings->pluck('id'))->delete();

            if (!empty($validated['force_unpublish'])) {
                TimetableSetting::whereIn('id', $publishedLocked->pluck('id'))
                    ->update(['is_published' => false, 'published_at' => null, 'published_by' => null]);
            }

            // One seed for the whole batch — reproducible as a unit, so
            // resending the same seed regenerates the identical whole-school
            // layout rather than each class re-randomizing independently.
            $usedSeed = $validated['seed'] ?? random_int(1, 2147483647);
            mt_srand($usedSeed);

            // Cross-setting occupancy, keyed by CLOCK TIME (see note above).
            // Each class in $settings accumulates its own placements into
            // these maps as the loop below progresses, so by the time SSS2
            // is generated, SSS1's freshly-placed slots already block it.
            $crossOccupied = [];
            $roomOccupied  = [];

            if ($includeRooms) {
                TimetableSlot::whereHas('setting', function ($q) use ($validated, $settings) {
                        $q->where('session_id', $validated['session_id'])->where('is_active', true)
                          ->whereNotIn('id', $settings->pluck('id'));
                        if (!empty($validated['term_id'])) $q->where('term_id', $validated['term_id']);
                        else                                $q->whereNull('term_id');
                    })
                    ->whereNotNull('room_id')->where('is_free', false)
                    ->with('period:id,start_time,end_time')
                    ->get(['room_id', 'period_id', 'day'])
                    ->each(function ($occ) use (&$roomOccupied) {
                        if (!$occ->period) return;
                        $roomOccupied[$occ->room_id][$occ->day][] = $this->periodTimeSignature($occ->period);
                    });
            }

            foreach ($ordered as $setting) {
                $stats = $this->runAutoGenerateCore($setting, $crossOccupied, $includeRooms, $roomOccupied);
                $setting->update([
                    'generation_seed'  => $usedSeed,
                    'generation_name'  => $validated['generation_name']  ?? $setting->generation_name,
                    'generation_notes' => $validated['generation_notes'] ?? $setting->generation_notes,
                    'generated_by'     => Auth::id(),
                    'generated_at'     => now(),
                ]);
                $results[] = [
                    'setting_id'     => $setting->id,
                    'class_name'     => $this->getClassName($setting->schoolclass),
                    'placed'         => $stats['placed'],
                    'unplaced'       => $stats['unplaced_subjects'],
                    'room_shortfall' => $stats['room_shortfall_count'] ?? 0,
                ];
            }

            $conflictSummary = $this->countConflictsForScope(
                $validated['session_id'], 
                $validated['term_id'] ?? null
            );

            mt_srand(); // reseed randomly so a persistent worker isn't stuck on this seed for unrelated requests

            DB::commit();
            return response()->json([
                'success'          => true,
                'message'          => 'Generated timetables for ' . count($results) . ' class(es).',
                'classes'          => $results,
                'had_shortfalls'   => collect($results)->contains(fn($r) => !empty($r['unplaced'])),
                'include_rooms'    => $includeRooms,
                'used_seed'        => $usedSeed,
                'conflict_summary' => $conflictSummary,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            mt_srand();
            Log::error('autoGenerateWholeSchool failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // PRIVATE: Core slot-placement logic
    //
    // NOTE ON $crossOccupied / $roomOccupied: these are keyed by
    // [teacherId/roomId][day][] = "HH:MM-HH:MM" time-signature strings, NOT
    // period_id. period_id is only meaningful within the setting that owns
    // the TimetablePeriod row — two different classes' "Period 3" are two
    // different rows with two different ids even though they're both
    // 09:20–10:00. Comparing raw period_id across settings (the original
    // bug) meant the generator could never see that a teacher was already
    // booked in a different class/arm at the same clock time.
    // =========================================================================
    private function runAutoGenerateCore(
        TimetableSetting $setting,
        array &$crossOccupied,
        bool $includeRooms = false,
        array &$roomOccupied = []
    ): array {
        $lessonPeriods = $setting->periods->where('type', 'lesson')->values();
        $days          = $setting->active_days ?? self::DAYS;
        $classId       = $setting->schoolclass_id;
        $sessionId     = $setting->session_id;

        $constraints = $this->ensureConstraintsExist($setting);

        $dayMeta     = $this->computeDayPeriodMeta($setting);

        // Randomize the pool order up front. usort()/Collection::sortBy()
        // are stable as of PHP 8, so shuffling here — and again when the
        // requirements are ordered below — is what makes two consecutive
        // "Generate" clicks produce different (but equally valid) results
        // instead of the same layout every time, since candidates that tie
        // on score keep this shuffled relative order rather than always
        // resolving to whichever slot happens to come first in the day/
        // period loop.
        $slotPool    = collect($this->buildWeightedSlotPool($days, $setting, $dayMeta))->shuffle()->values()->all();
        $totalSlots  = count($slotPool);
        $freeTarget  = $setting->free_periods_per_week ?? 0;

        // Admin-selected forced-free slots for this specific class/arm (see
        // saveFreePeriods()) — these are excluded from placement entirely,
        // not just budgeted around, so the admin's explicit choice of WHEN
        // a class is free is always honoured regardless of what the
        // randomizer would otherwise have picked.
        $forcedFreeKeys = [];
        foreach (($setting->preferred_free_slots ?? []) as $pf) {
            if (!empty($pf['day']) && !empty($pf['period_id'])) {
                $forcedFreeKeys[$pf['day'] . '_' . $pf['period_id']] = true;
            }
        }

        $placementBudget = max(0, $totalSlots - max($freeTarget, count($forcedFreeKeys)));

        $subjectTeachers = SubjectTeacher::where('sessionid', $sessionId)
            ->when($setting->term_id, fn($q) => $q->where('termid', $setting->term_id))
            ->whereHas('subjectclass', fn($q) => $q->where('schoolclassid', $classId))
            ->with(['subject', 'staff'])
            ->get()
            ->groupBy('subjectid');

        // Prefer the smallest room that still fits the class, rather than
        // the alphabetically-first free room regardless of size — avoids
        // parking a 15-student class in the largest hall while a class of
        // 60 gets squeezed into a room that's actually too small. Falls
        // back to plain alphabetical order when we have no enrollment data
        // to estimate class size from, so this never blocks generation.
        $availableRoomIds = [];
        if ($includeRooms) {
            $classSize = $this->estimateClassSize($classId, $sessionId, $setting->term_id);
            $rooms     = Room::where('is_active', true)->get(['id', 'room_name', 'capacity']);

            $availableRoomIds = $classSize
                ? $rooms->sortBy(fn($r) => ($r->capacity && $r->capacity >= $classSize) ? $r->capacity : (100000 + ($r->capacity ?? 99999)))->pluck('id')->toArray()
                : $rooms->sortBy('room_name')->pluck('id')->toArray();
        }

        $availabilityMap = $this->loadTeacherAvailability($subjectTeachers);

        $teacherDaySlot = [];
        $placed = [];
        $unplacedSubjects = [];
        $roomShortfallCount = 0;
        $lessonsPlacedByDay = [];
        $maxPerDay = $setting->max_lessons_per_day ?? null;

        // Shuffle before sorting by periods_per_week — the sort is stable,
        // so subjects with an equal periods_per_week value still process in
        // a randomized order each run instead of always following their
        // database id/insertion order (another source of "always identical
        // output" in the original version).
        $requirements = $constraints
            ->shuffle()
            ->sortBy(fn($c) => $c->periods_per_week)
            ->values();

        foreach ($requirements as $constraint) {
            $subjectId = $constraint->subject_id;
            $needed = $constraint->periods_per_week;
            $allowDouble = $constraint->allow_double_period;
            $maxDouble = $constraint->max_double_periods_per_week;
            $preferDays = $constraint->preferred_days ?? [];
            $avoidDays = $constraint->avoid_days ?? [];
            $avoidConsecutiveDoubles = $constraint->avoid_consecutive_double_days ?? true;

            $teacherEntry = $subjectTeachers->get($subjectId)?->first();
            $teacherId = $teacherEntry?->staffid;

            $alreadyPlaced = $this->countSubjectPlaced($placed, $subjectId);
            if ($alreadyPlaced >= $needed) continue;

            $candidates = [];
            foreach ($slotPool as $slot) {
                $day = $slot['day'];
                $periodId = $slot['period_id'];
                $timeSig = $slot['time_sig'];
                $key = $day . '_' . $periodId;

                if (isset($placed[$key])) continue;
                if (isset($forcedFreeKeys[$key])) continue;

                if ($teacherId) {
                    if (in_array($periodId, $teacherDaySlot[$teacherId][$day] ?? [])) continue;
                    if (in_array($timeSig, $crossOccupied[$teacherId][$day] ?? [])) continue;
                    if (!$this->isTeacherAvailableForPeriod($teacherId, $day, $periodId, $setting, $availabilityMap)) continue;
                }

                if ($maxPerDay && ($lessonsPlacedByDay[$day] ?? 0) >= $maxPerDay) continue;

                $score = 0;
                if (in_array($day, $preferDays)) $score += 20;
                if (in_array($day, $avoidDays)) $score -= 15;
                if ($setting->deprioritize_break_adjacent && !empty($slot['is_break_adjacent'])) $score -= 5;

                $currentDayLoad = $lessonsPlacedByDay[$day] ?? 0;
                $score -= ($currentDayLoad * 2);

                // Small random jitter so candidates that would otherwise
                // tie exactly on score don't always resolve in the same
                // order — this, combined with the slotPool/requirements
                // shuffle above, is what makes each "Generate" run produce
                // a genuinely different layout.
                $score += mt_rand(-3, 3);

                $candidates[] = [
                    'slot' => $slot,
                    'score' => $score,
                    'key' => $key
                ];
            }

            usort($candidates, fn($a, $b) => $b['score'] - $a['score']);

            $placedThisSubject = $alreadyPlaced;
            $doubleCount = 0;
            $usedDoubleDays = [];
            $remaining = $needed - $alreadyPlaced;

            foreach ($candidates as $candidate) {
                if ($placedThisSubject >= $needed) break;
                if (count($placed) >= $placementBudget) break 2;

                $slot = $candidate['slot'];
                $day = $slot['day'];
                $periodId = $slot['period_id'];
                $timeSig = $slot['time_sig'];
                $key = $candidate['key'];

                if (isset($placed[$key])) continue;
                if ($maxPerDay && ($lessonsPlacedByDay[$day] ?? 0) >= $maxPerDay) continue;

                $roomId = $includeRooms
                    ? $this->pickAvailableRoom($availableRoomIds, $day, $timeSig, $roomOccupied)
                    : null;
                if ($includeRooms && !$roomId) $roomShortfallCount++;

                TimetableSlot::create([
                    'setting_id' => $setting->id,
                    'period_id' => $periodId,
                    'day' => $day,
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                    'room_id' => $roomId,
                    'is_double' => false,
                    'is_free' => false,
                ]);

                $placed[$key] = $subjectId;
                $lessonsPlacedByDay[$day] = ($lessonsPlacedByDay[$day] ?? 0) + 1;
                $placedThisSubject++;

                if ($teacherId) {
                    $teacherDaySlot[$teacherId][$day][] = $periodId;
                    $crossOccupied[$teacherId][$day][] = $timeSig;
                }
                if ($roomId) {
                    $roomOccupied[$roomId][$day][] = $timeSig;
                }

                if ($allowDouble && $doubleCount < $maxDouble && $placedThisSubject < $needed) {
                    $cooldownOk = true;
                    if ($avoidConsecutiveDoubles) {
                        foreach ($usedDoubleDays as $usedDay) {
                            if (abs((self::DAYS_MAP[$day] ?? 0) - (self::DAYS_MAP[$usedDay] ?? 0)) <= 1) {
                                $cooldownOk = false;
                                break;
                            }
                        }
                    }

                    if ($cooldownOk) {
                        $nextPeriod = $this->getNextLessonPeriod($lessonPeriods, $periodId);
                        $nextApplicable = $nextPeriod
                            && ($dayMeta[$day][$nextPeriod->id]['effective_type'] ?? null) === 'lesson'
                            && ($dayMeta[$day][$nextPeriod->id]['applicable'] ?? false);

                        if ($nextPeriod && $nextApplicable) {
                            $nextKey = $day . '_' . $nextPeriod->id;
                            $nextTimeSig = $this->periodTimeSignature($nextPeriod);

                            $teacherConflict = $teacherId && (
                                in_array($nextPeriod->id, $teacherDaySlot[$teacherId][$day] ?? []) ||
                                in_array($nextTimeSig, $crossOccupied[$teacherId][$day] ?? [])
                            );

                            $teacherAvailableNext = !$teacherId
                                || $this->isTeacherAvailableForPeriod(
                                    $teacherId, $day, $nextPeriod->id, $setting, $availabilityMap
                                );

                            if (!isset($placed[$nextKey]) && !isset($forcedFreeKeys[$nextKey]) && !$teacherConflict && $teacherAvailableNext) {
                                $nextRoomId = $roomId;
                                if ($includeRooms && $nextRoomId
                                    && in_array($nextTimeSig, $roomOccupied[$nextRoomId][$day] ?? [])
                                ) {
                                    $nextRoomId = $this->pickAvailableRoom(
                                        $availableRoomIds, $day, $nextTimeSig, $roomOccupied
                                    );
                                    if (!$nextRoomId) $roomShortfallCount++;
                                }

                                TimetableSlot::create([
                                    'setting_id' => $setting->id,
                                    'period_id' => $nextPeriod->id,
                                    'day' => $day,
                                    'subject_id' => $subjectId,
                                    'teacher_id' => $teacherId,
                                    'room_id' => $nextRoomId,
                                    'is_double' => true,
                                    'is_free' => false,
                                ]);

                                $placed[$nextKey] = $subjectId;
                                $lessonsPlacedByDay[$day] = ($lessonsPlacedByDay[$day] ?? 0) + 1;

                                if ($teacherId) {
                                    $teacherDaySlot[$teacherId][$day][] = $nextPeriod->id;
                                    $crossOccupied[$teacherId][$day][] = $nextTimeSig;
                                }
                                if ($nextRoomId) {
                                    $roomOccupied[$nextRoomId][$day][] = $nextTimeSig;
                                }

                                $placedThisSubject++;
                                $doubleCount++;
                                $usedDoubleDays[] = $day;
                            }
                        }
                    }
                }
            }

            if ($placedThisSubject < $needed) {
                $unplacedSubjects[] = [
                    'subject' => $constraint->subject?->subject ?? "Subject #{$subjectId}",
                    'needed' => $needed,
                    'placed' => $placedThisSubject,
                ];
            }
        }

        foreach ($slotPool as $slot) {
            $key = $slot['day'] . '_' . $slot['period_id'];
            if (!isset($placed[$key])) {
                TimetableSlot::create([
                    'setting_id' => $setting->id,
                    'period_id' => $slot['period_id'],
                    'day' => $slot['day'],
                    'subject_id' => null,
                    'teacher_id' => null,
                    'is_free' => true,
                ]);
            }
        }

        return [
            'placed' => count($placed),
            'unplaced_subjects' => $unplacedSubjects,
            'rooms_included' => $includeRooms,
            'room_shortfall_count' => $roomShortfallCount,
        ];
    }

    // =========================================================================
    // PRIVATE: Count how many times a subject is already placed
    // =========================================================================
    private function countSubjectPlaced(array $placed, int $subjectId): int
    {
        $count = 0;
        foreach ($placed as $sid) {
            if ($sid === $subjectId) $count++;
        }
        return $count;
    }

    // =========================================================================
    // PRIVATE: Load teacher availability data
    // =========================================================================
    private function loadTeacherAvailability($subjectTeachers): array
    {
        $availabilityMap = [];
        $teacherIds = $subjectTeachers->flatten()->pluck('staffid')->filter()->unique()->values();
        if ($teacherIds->isNotEmpty()) {
            TeacherAvailability::whereIn('teacher_id', $teacherIds)->get()
                ->each(function ($a) use (&$availabilityMap) {
                    $availabilityMap[$a->teacher_id][$a->day][] = [
                        'start' => $a->start_time,
                        'end' => $a->end_time,
                        'is_available' => (bool) $a->is_available,
                    ];
                });
        }
        return $availabilityMap;
    }

    // =========================================================================
    // PRIVATE: Rough class-size estimate for capacity-aware room picking.
    // We have no direct "class roll" model, so this approximates class size
    // as the largest per-subject registration count for the class — most
    // schools have at least one subject (core/compulsory) taken by nearly
    // the whole class, so the max is a reasonable stand-in. Returns null
    // (never blocks generation) when there's no registration data yet.
    // =========================================================================
    private function estimateClassSize(int $classId, int $sessionId, ?int $termId): ?int
    {
        $subjectclassIds = Subjectclass::where('schoolclassid', $classId)->pluck('id');
        if ($subjectclassIds->isEmpty()) return null;

        $max = SubjectRegistrationStatus::whereIn('subjectclassid', $subjectclassIds)
            ->where('sessionid', $sessionId)
            ->when($termId, fn($q) => $q->where('termid', $termId))
            ->select(DB::raw('COUNT(DISTINCT studentid) as cnt'))
            ->groupBy('subjectclassid')
            ->pluck('cnt')
            ->max();

        return $max ? (int) $max : null;
    }

    // =========================================================================
    // PRIVATE: Ensure a setting has at least one constraint row
    // =========================================================================
    private function ensureConstraintsExist(TimetableSetting $setting): \Illuminate\Support\Collection
    {
        $constraints = $setting->constraints->keyBy('subject_id');

        if ($constraints->isNotEmpty()) {
            return $constraints;
        }

        $subjectTeachers = SubjectTeacher::where('sessionid', $setting->session_id)
            ->when($setting->term_id, fn ($q) => $q->where('termid', $setting->term_id))
            ->whereHas('subjectclass', fn ($q) => $q->where('schoolclassid', $setting->schoolclass_id))
            ->get()
            ->unique('subjectid')
            ->values();

        if ($subjectTeachers->isEmpty()) {
            return $constraints;
        }

        $dayMeta = $this->computeDayPeriodMeta($setting);
        $totalLessonSlots = 0;
        foreach ($dayMeta as $periodsForDay) {
            foreach ($periodsForDay as $meta) {
                if ($meta['applicable'] && $meta['effective_type'] === 'lesson') {
                    $totalLessonSlots++;
                }
            }
        }

        $freeTarget = $setting->free_periods_per_week ?? 0;
        // Admin-forced-free slots (see saveFreePeriods) are guaranteed free
        // regardless of free_periods_per_week — the default-constraint
        // budget must account for whichever number is larger, or the
        // auto-generated periods_per_week values can overshoot what
        // runAutoGenerateCore's placementBudget will actually allow.
        $forcedFreeCount = count($setting->preferred_free_slots ?? []);
        $budget = max(0, $totalLessonSlots - max($freeTarget, $forcedFreeCount));

        if ($budget === 0) {
            $budget = max(1, count($subjectTeachers) * 2);
        }

        $subjectCount = $subjectTeachers->count();
        
        $base = $subjectCount > 0 ? intdiv($budget, $subjectCount) : 0;
        $base = max(1, min($base, 8));
        $remainder = $budget - ($base * $subjectCount);

        $created = 0;
        foreach ($subjectTeachers->values() as $i => $st) {
            if (TimetableConstraint::where('setting_id', $setting->id)
                    ->where('subject_id', $st->subjectid)
                    ->exists()) {
                continue;
            }

            $periodsPerWeek = $base + ($i < $remainder ? 1 : 0);

            TimetableConstraint::create([
                'setting_id'                    => $setting->id,
                'subject_id'                    => $st->subjectid,
                'periods_per_week'              => $periodsPerWeek,
                'allow_double_period'           => false,
                'max_double_periods_per_week'   => 1,
                'is_compulsory'                 => true,
                'avoid_consecutive_double_days' => true,
            ]);
            $created++;
        }

        if ($created > 0) {
            $setting->load('constraints.subject');
        }

        return $setting->constraints->keyBy('subject_id');
    }

    // =========================================================================
    // PRIVATE: First free room for a given day/clock-time (keyed by time
    // signature — see note on $roomOccupied in runAutoGenerateCore).
    // =========================================================================
    private function pickAvailableRoom(array $availableRoomIds, string $day, string $timeSig, array &$roomOccupied): ?int
    {
        foreach ($availableRoomIds as $roomId) {
            if (!in_array($timeSig, $roomOccupied[$roomId][$day] ?? [])) {
                return $roomId;
            }
        }
        return null;
    }

    // =========================================================================
    // PRIVATE: Is this teacher free for a given day/period?
    // =========================================================================
    private function isTeacherAvailableForPeriod(int $teacherId, string $day, int $periodId, TimetableSetting $setting, array $availabilityMap): bool
    {
        $windows = $availabilityMap[$teacherId][$day] ?? null;
        if (!$windows) return true;

        $period = $setting->periods->firstWhere('id', $periodId);
        if (!$period) return true;

        foreach ($windows as $w) {
            if (!$w['is_available']) continue;
            if ($period->start_time >= $w['start'] && $period->end_time <= $w['end']) return true;
        }
        return false;
    }

    private function buildWeightedSlotPool($days, TimetableSetting $setting, array $dayMeta): array
    {
        $pool = [];
        foreach ($days as $day) {
            foreach ($setting->periods as $period) {
                $m = $dayMeta[$day][$period->id] ?? null;
                if (!$m || $m['effective_type'] !== 'lesson' || !$m['applicable']) continue;

                $pool[] = [
                    'day'               => $day,
                    'period_id'         => $period->id,
                    'period_order'      => $period->order,
                    'time_sig'          => $this->periodTimeSignature($period),
                    'is_break_adjacent' => $this->isBreakAdjacent($period, $setting->periods),
                ];
            }
        }
        return $pool;
    }

    private function getNextLessonPeriod($lessonPeriods, int $currentPeriodId)
    {
        $found = false;
        foreach ($lessonPeriods as $p) {
            if ($found) return $p;
            if ($p->id === $currentPeriodId) $found = true;
        }
        return null;
    }

    // =========================================================================
    // TEACHER ASSIGNMENTS — READ-ONLY
    // =========================================================================
    public function getTeacherAssignments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:schoolsession,id',
            'term_id'    => 'nullable|exists:schoolterm,id',
        ]);

        try {
            $query = Subjectclass::query()
                ->leftJoin('subjectteacher', 'subjectteacher.id', '=', 'subjectclass.subjectteacherid')
                ->leftJoin('subject', 'subject.id', '=', 'subjectteacher.subjectid')
                ->leftJoin('users', 'users.id', '=', 'subjectteacher.staffid')
                ->leftJoin('schoolclass', 'schoolclass.id', '=', 'subjectclass.schoolclassid')
                ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                ->where('subjectteacher.sessionid', $validated['session_id']);

            if (!empty($validated['term_id'])) {
                $query->where('subjectteacher.termid', $validated['term_id']);
            }

            $assignments = $query->select([
                'subjectclass.id as subjectclass_id',
                'subjectclass.subjectteacherid',
                'subject.id as subject_id',
                'subject.subject as subject_name',
                'subject.subject_code as subject_code',
                'schoolclass.id as schoolclass_id',
                'schoolclass.schoolclass as class_name',
                'schoolarm.arm as arm_name',
                'users.id as teacher_id',
                'users.name as teacher_name',
            ])
            ->orderBy('schoolclass.schoolclass')
            ->orderBy('schoolarm.arm')
            ->orderBy('subject.subject')
            ->get();

            if ($assignments->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'teachers' => [],
                    'unassigned' => [],
                    'message' => 'No subject-class assignments found for this session/term.'
                ]);
            }

            $subjectclassIds = $assignments->pluck('subjectclass_id')->unique()->values();
            $studentCounts = SubjectRegistrationStatus::whereIn('subjectclassid', $subjectclassIds)
                ->where('sessionid', $validated['session_id'])
                ->when(!empty($validated['term_id']), fn($q, $termId) => $q->where('termid', $termId))
                ->select(['subjectclassid', DB::raw('COUNT(DISTINCT studentid) as cnt')])
                ->groupBy('subjectclassid')
                ->pluck('cnt', 'subjectclassid');

            $teacherIds = $assignments->pluck('teacher_id')->filter()->unique()->values();
            $teacherPictures = [];
            if ($teacherIds->isNotEmpty()) {
                $pictures = DB::table('staffpicture')
                    ->whereIn('staffid', $teacherIds)
                    ->get(['staffid', 'picture']);
                
                foreach ($pictures as $pic) {
                    $teacherPictures[$pic->staffid] = $pic->picture;
                }
            }

            $byTeacher = [];
            $unassigned = [];

            foreach ($assignments as $row) {
                $className = trim(($row->class_name ?? '') . ' ' . ($row->arm_name ?? ''));
                
                $data = [
                    'subjectclass_id' => $row->subjectclass_id,
                    'subject_id' => $row->subject_id,
                    'subject_name' => $row->subject_name ?? 'Unknown Subject',
                    'subject_code' => $row->subject_code ?? '',
                    'schoolclass_id' => $row->schoolclass_id,
                    'class_name' => $className ?: 'Unknown Class',
                    'registered_count' => (int) ($studentCounts[$row->subjectclass_id] ?? 0),
                ];

                if ($row->teacher_id) {
                    if (!isset($byTeacher[$row->teacher_id])) {
                        $byTeacher[$row->teacher_id] = [
                            'teacher_id' => $row->teacher_id,
                            'teacher_name' => $row->teacher_name ?? 'Unknown Teacher',
                            'teacher_picture' => isset($teacherPictures[$row->teacher_id]) 
                                ? asset('storage/staff_avatars/' . $teacherPictures[$row->teacher_id])
                                : asset('storage/staff_avatars/default.png'),
                            'assignments' => [],
                        ];
                    }
                    $byTeacher[$row->teacher_id]['assignments'][] = $data;
                } else {
                    $unassigned[] = $data;
                }
            }

            return response()->json([
                'success' => true,
                'teachers' => array_values($byTeacher),
                'unassigned' => $unassigned,
            ]);

        } catch (\Exception $e) {
            Log::error('getTeacherAssignments failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'validated' => $validated
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load teacher assignments: ' . $e->getMessage(),
                'teachers' => [],
                'unassigned' => [],
            ], 500);
        }
    }

    // =========================================================================
    // TEACHER VIEW — with Session, Term, and Class filtering
    // =========================================================================
   public function teacherView(Request $request)
    {
        $teacherId = Auth::id();
        $pagetitle = 'My Timetable';

        // Get filters from request
        $sessionId = $request->input('session_id') 
            ?? Schoolsession::where('status', 'Current')->value('id') 
            ?? Schoolsession::latest('id')->value('id');
        
        $termId = $request->input('term_id') 
            // Term IDs are NOT scoped per-session, so "latest id" can pick a
            // term row belonging to a totally different session — even one
            // that happens to share the same display name (e.g. another
            // session's own "Third Term"). That mismatched term_id then
            // matches zero TimetableSettings for THIS session, leaving the
            // grid empty while unfiltered widgets (upcoming/weekly, which
            // never filtered by term) kept showing data. Prefer the term
            // actually flagged current; only fall back to "latest id" if
            // nothing is flagged.
            ?? Schoolterm::where('status', true)->value('id')
            ?? Schoolterm::latest('id')->value('id');
        
        $classId = $request->input('class_id');

        $teacher = User::with('staffPicture')->find($teacherId);
        $teacherPicture = $teacher?->staffPicture
            ? asset('storage/staff_avatars/' . $teacher->staffPicture->picture) 
            : null;

        // Get all classes this teacher teaches — with the arm attached and
        // resolved so "SSS 1" doesn't silently collapse two different arms
        // (SSS 1 arm4, SSS 1 arm5, SSS 1 arm8...) into one indistinguishable
        // dropdown entry.
        $teacherClasses = SubjectTeacher::where('staffid', $teacherId)
            ->whereHas('subjectclass', fn($q) => $q->whereNotNull('schoolclassid'))
            ->with(['subjectclass.schoolclass', 'subjectclass.schoolclass.armRelation'])
            ->get()
            ->pluck('subjectclass.schoolclass')
            ->filter()
            ->unique('id')
            ->values()
            ->map(function ($class) {
                $class->full_name = $this->getClassName($class);
                return $class;
            });

        // Build the slots query with filters
        $slotsQuery = TimetableSlot::where('teacher_id', $teacherId)
            ->whereHas('setting', function($q) use ($sessionId, $termId) {
                $q->where('session_id', $sessionId)->where('is_active', true);
                if ($termId) $q->where('term_id', $termId);
            })
            ->whereNotNull('subject_id')
            ->with(['period', 'subject', 'setting.schoolclass', 'setting.schoolclass.armRelation', 'setting.term', 'room']);

        // Apply class filter if selected
        if ($classId) {
            $slotsQuery->whereHas('setting', function($q) use ($classId) {
                $q->where('schoolclass_id', $classId);
            });
        }

        // IMPORTANT: Get the slots as a collection, NOT JSON
        $slotsCollection = $slotsQuery->get();

        // Attach display-safe, arm-aware class names and a day/time
        // signature to every slot up front — the grid, alerts, and
        // conflict detection below all key off these instead of the raw
        // "schoolclass" column (which drops the arm) or period_id (which
        // isn't comparable across settings).
        foreach ($slotsCollection as $slot) {
            $slot->class_full_name = $this->getClassName($slot->setting?->schoolclass);
            $slot->time_sig = $slot->period ? $this->periodTimeSignature($slot->period) : null;
        }

        // Group by day
        $slots = $slotsCollection->groupBy('day');

        // Calculate combined sessions and conflicts by CLOCK TIME, not
        // period_id. Two different class arms taught by this teacher at
        // 09:20–10:00 have two different period_id values (one per
        // setting) — grouping by period_id, as before, meant a genuine
        // double-booking across arms was never detected and only one of
        // the two classes ever rendered in the grid cell.
        $combinedMap = [];
        foreach ($slotsCollection as $slot) {
            if (!$slot->time_sig) continue;
            $key = $slot->teacher_id . '|' . $slot->day . '|' . $slot->time_sig;
            $combinedMap[$key][] = $slot;
        }

        foreach ($combinedMap as $key => $slotGroup) {
            $count = count($slotGroup);
            if ($count <= 1) {
                $slotGroup[0]->combined_count = 1;
                $slotGroup[0]->conflict_count = 0;
                continue;
            }

            foreach ($slotGroup as $i => $slotA) {
                $conflictWithAnother = false;
                foreach ($slotGroup as $j => $slotB) {
                    if ($i === $j) continue;
                    if (!$this->isCombinedSession($slotA, $slotB)) {
                        $conflictWithAnother = true;
                        break;
                    }
                }
                $slotA->combined_count = $count;
                $slotA->conflict_count = $conflictWithAnother ? $count : 0;
            }
        }

        // Groups that represent a REAL conflict (different classes, not a
        // legitimate same-teacher/subject/room combined session) — used to
        // drive the top-of-page alert and stat card.
        $conflictGroups = collect($combinedMap)
            ->map(fn($group) => collect($group))
            ->filter(fn($group) => $group->contains(fn($s) => ($s->conflict_count ?? 0) > 0));

        // Get all periods across settings (for the grid) — deduplicated by
        // actual CLOCK TIME rather than the "order" column, since "order"
        // is only guaranteed consistent within one setting's own period
        // list, not across every class/arm's independently-built schedule.
        $allPeriods = TimetablePeriod::with('setting')
            ->whereIn(
                'setting_id',
                TimetableSetting::where('session_id', $sessionId)->pluck('id')
            )
            ->orderBy('start_time')
            ->get()
            ->unique(fn($p) => substr($p->start_time, 0, 5) . '|' . substr($p->end_time, 0, 5))
            ->values();

        // Build day/period meta
        $periodDayMeta = [];
        $metaCache = [];
        foreach ($allPeriods as $period) {
            if (!$period->setting) continue;
            $sid = $period->setting_id;
            if (!isset($metaCache[$sid])) {
                $metaCache[$sid] = $this->computeDayPeriodMeta($period->setting->load('periods'));
            }
            foreach (self::DAYS as $day) {
                $periodDayMeta[$period->id][$day] = $metaCache[$sid][$day][$period->id]
                    ?? ['applicable' => true, 'effective_type' => $period->type];
            }
        }

        $sessions = Schoolsession::orderByDesc('id')->get();
        $terms = Schoolterm::all();
        $days = self::DAYS;
        $upcomingSlots = $this->getUpcomingSlots($teacherId, $sessionId, $termId);
        $weeklySummary = $this->getWeeklySummary($teacherId, $sessionId, $termId);
        $todaySlots = $slots[date('l')] ?? collect();

        $icsUrl = URL::signedRoute('timetable.ics', ['teacherId' => $teacherId], now()->addYears(10));
        $webcalUrl = preg_replace('/^https?:\/\//', 'webcal://', $icsUrl);

        return view('timetable.teacher', compact(
            'pagetitle', 'slots', 'days', 'allPeriods', 'sessions', 'terms',
            'sessionId', 'termId', 'classId', 'teacherClasses',
            'upcomingSlots', 'weeklySummary', 'teacherPicture',
            'periodDayMeta', 'icsUrl', 'webcalUrl', 'todaySlots', 'conflictGroups'
        ));
    }
    // =========================================================================
    // PRIVATE: Upcoming slots / weekly summary for teacher view
    // =========================================================================
    private function getUpcomingSlots(int $teacherId, int $sessionId, ?int $termId = null): array
    {
        $dayMap     = ['monday' => 0, 'tuesday' => 1, 'wednesday' => 2, 'thursday' => 3, 'friday' => 4];
        $today      = strtolower(now()->format('l'));
        $todayIndex = $dayMap[$today] ?? 0;
        $now        = Carbon::now();

        $slots = TimetableSlot::where('teacher_id', $teacherId)
            ->whereHas('setting', function ($q) use ($sessionId, $termId) {
                $q->where('session_id', $sessionId)->where('is_active', true);
                if ($termId) $q->where('term_id', $termId);
            })
            ->whereNotNull('subject_id')
            ->with(['period', 'subject', 'setting.schoolclass', 'setting.schoolclass.armRelation', 'setting.term', 'room'])->get();

        return $slots
            ->map(function ($slot) use ($dayMap, $todayIndex, $now, $today) {
                $dIdx      = $dayMap[strtolower($slot->day)] ?? 0;
                $daysAhead = $dIdx >= $todayIndex ? $dIdx - $todayIndex : ($dIdx + 7 - $todayIndex);

                if ($daysAhead === 0 && strtolower($slot->day) === $today) {
                    try {
                        if (Carbon::createFromFormat('H:i:s', $slot->period->start_time)->lessThanOrEqualTo($now)) {
                            $daysAhead = 7;
                        }
                    } catch (\Exception $e) { /* keep as-is */ }
                }

                return ['slot' => $slot, 'occur_date' => $now->copy()->addDays($daysAhead)->startOfDay(), 'sort' => $daysAhead];
            })
            ->filter(function ($item) {
                $slot    = $item['slot'];
                $holiday = $this->getHolidayForDate($item['occur_date'], $slot->setting?->session_id, $slot->setting?->term_id);
                if (!$holiday) return true;
                if ($holiday->is_full_day) return false;

                if ($holiday->cutoff_time) {
                    try {
                        $periodStart = Carbon::createFromFormat('H:i:s', $slot->period->start_time);
                        $cutoff      = Carbon::createFromFormat('H:i:s', $holiday->cutoff_time);
                        if ($periodStart->greaterThanOrEqualTo($cutoff)) return false;
                    } catch (\Exception $e) { /* keep as-is */ }
                }
                return true;
            })
            ->sortBy('sort')
            ->take(6)
            ->map(fn($item) => [
                'day'     => $item['slot']->day,
                'date'    => $item['occur_date']->format('D, d M'),
                'period'  => $item['slot']->period?->name,
                'time'    => $this->formatTime($item['slot']->period?->start_time ?? '') . ' – ' . $this->formatTime($item['slot']->period?->end_time ?? ''),
                'subject' => $item['slot']->subject?->subject,
                'class'   => $this->getClassName($item['slot']->setting?->schoolclass),
                'room'    => $item['slot']->room?->room_name,
            ])
            ->values()->toArray();
    }

    private function getWeeklySummary(int $teacherId, int $sessionId, ?int $termId = null): array
    {
        $slots = TimetableSlot::where('teacher_id', $teacherId)
            ->whereHas('setting', function ($q) use ($sessionId, $termId) {
                $q->where('session_id', $sessionId)->where('is_active', true);
                if ($termId) $q->where('term_id', $termId);
            })
            ->whereNotNull('subject_id')
            ->with(['period', 'subject', 'setting.schoolclass', 'setting.schoolclass.armRelation', 'room'])->get();

        $summary = [];
        foreach (self::DAYS as $day) {
            $ds = $slots->where('day', $day);
            $summary[$day] = [
                'count'    => $ds->count(),
                'subjects' => $ds->pluck('subject.subject')->filter()->unique()->values()->toArray(),
                'classes'  => $ds->map(fn($s) => $this->getClassName($s->setting?->schoolclass))->filter()->unique()->values()->toArray(),
            ];
        }
        return $summary;
    }

    // =========================================================================
    // EXPORT TEACHER TIMETABLE — CSV
    // =========================================================================
    public function exportTeacherTimetable(Request $request)
    {
        $teacherId = Auth::id();
        $sessionId = $request->input('session_id') ?? Schoolsession::where('status', 'Current')->value('id');
        $termId = $request->input('term_id')
            ?? Schoolterm::where('status', true)->value('id')
            ?? Schoolterm::latest('id')->value('id');
        $classId = $request->input('class_id');

        $query = TimetableSlot::where('teacher_id', $teacherId)
            ->whereHas('setting', function($q) use ($sessionId, $termId) {
                $q->where('session_id', $sessionId)->where('is_active', true);
                if ($termId) $q->where('term_id', $termId);
            })
            ->whereNotNull('subject_id')
            ->with(['period', 'subject', 'setting.schoolclass', 'setting.schoolclass.armRelation', 'room']);

        if ($classId) {
            $query->whereHas('setting', fn($q) => $q->where('schoolclass_id', $classId));
        }

        $slots = $query->get();

        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['Day', 'Period', 'Start', 'End', 'Subject', 'Class', 'Room', 'Double']);

        foreach ($slots as $slot) {
            fputcsv($handle, [
                $slot->day,
                $slot->period?->name,
                substr($slot->period?->start_time ?? '', 0, 5),
                substr($slot->period?->end_time ?? '', 0, 5),
                $slot->subject?->subject,
                $this->getClassName($slot->setting?->schoolclass),
                $slot->room?->room_name,
                $slot->is_double ? 'Yes' : 'No',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="my-timetable-' . date('Y-m-d') . '.csv"');
    }

    private function scheduleNotification(int $teacherId, int $slotId, string $type): void
    {
        $teacher = User::find($teacherId);
        if (!$teacher || !$teacher->email) return;
        $slot = TimetableSlot::with(['period', 'subject', 'room'])->find($slotId);
        if (!$slot) return;

        TimetableNotification::create([
            'teacher_id'   => $teacherId,
            'slot_id'      => $slotId,
            'type'         => $type,
            'email'        => $teacher->email,
            'scheduled_at' => now(),
            'status'       => 'pending',
            'payload'      => json_encode([
                'day'     => $slot->day,
                'period'  => $slot->period?->name,
                'subject' => $slot->subject?->subject,
                'room'    => $slot->room?->room_name,
            ]),
        ]);
    }

    private function logTimetableChange(int $userId, string $action, string $modelType, ?int $modelId, $oldValues = null, $newValues = null): void
    {
        DB::table('timetable_audit_logs')->insert([
            'user_id'    => $userId,
            'action'     => $action,
            'model_type' => $modelType,
            'model_id'   => $modelId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // =========================================================================
    // EXPORT HELPERS (kept from original)
    // =========================================================================
    private function exportCsv($setting, $periods, $days, $grid, $className, $sessionName, $dayMeta)
    {
        $handle = fopen('php://temp', 'w+');
        $header = ['Period'];
        foreach ($days as $d) $header[] = $d;
        fputcsv($handle, $header);

        foreach ($periods as $period) {
            $row = [$period->name . ' (' . $this->formatTime($period->start_time) . '–' . $this->formatTime($period->end_time) . ')'];
            foreach ($days as $day) {
                $meta = $dayMeta[$day][$period->id] ?? null;
                if (!$meta || $meta['effective_type'] !== 'lesson' || !$meta['applicable']) {
                    $row[] = '—';
                    continue;
                }
                $slot = $grid[$period->id][$day] ?? null;
                if (!$slot || $slot['is_free']) {
                    $row[] = 'FREE';
                } else {
                    $parts = array_filter([$slot['subject'] ?? '', $slot['teacher'] ?? '', $slot['room'] ?? '']);
                    $row[] = implode(' | ', $parts);
                }
            }
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        $filename = 'timetable-' . str_replace(' ', '-', $className) . '-' . date('Y-m-d') . '.csv';
        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    private function exportPdf($setting, $periods, $days, $grid, $subjectColors, $className, $sessionName, $termName, $orientation, $dayMeta)
    {
        $pdf = Pdf::loadView('timetable.exports.pdf', compact(
            'setting', 'periods', 'days', 'grid', 'subjectColors',
            'className', 'sessionName', 'termName', 'orientation', 'dayMeta'
        ))->setPaper($orientation === 'vertical' ? 'a4' : 'a3', 'landscape');

        return $pdf->stream('timetable-' . str_replace(' ', '-', $className) . '.pdf');
    }

    private function exportWholeSchoolPdf(array $allTimetables, ?SchoolInformation $schoolInfo, ?Schoolsession $session, ?Schoolterm $term, string $orientation, array $overallStats = [])
    {
        $sessionName = $session->session ?? 'Session';
        $termName    = $term?->term ?? 'All Terms';

        $pdf = Pdf::loadView('timetable.exports.whole-school', [
            'allTimetables' => $allTimetables,
            'schoolInfo'    => $schoolInfo,
            'sessionName'   => $sessionName,
            'termName'      => $termName,
            'orientation'   => $orientation,
            'dayColors'     => self::DAY_COLORS,
            'generatedAt'   => now()->format('d M Y, H:i'),
            'overallStats'  => $overallStats,
        ])->setPaper($orientation === 'vertical' ? 'a4' : 'a3', 'landscape');

        $filename = 'whole-school-timetable-' . str_replace([' ', '/'], '-', $sessionName) . '.pdf';
        return $pdf->stream($filename);
    }

    // =========================================================================
    // PRIVATE: Staff analytics for a session/term scope — per-teacher
    // workload, class/subject/room frequency, daily load, and genuine
    // conflict count (grouped by CLOCK TIME, same rule as everywhere else
    // in this controller — see periodTimeSignature()). Feeds the staff
    // dropdown + cross-highlight on the merged grid and whole-school views.
    // =========================================================================
    private function buildStaffAnalytics(int $sessionId, ?int $termId): array
    {
        $slots = TimetableSlot::whereHas('setting', function ($q) use ($sessionId, $termId) {
                $q->where('session_id', $sessionId)->where('is_active', true);
                if ($termId) $q->where('term_id', $termId);
                else         $q->whereNull('term_id');
            })
            ->whereNotNull('teacher_id')
            ->where('is_free', false)
            ->whereNotNull('subject_id')
            ->with(['period', 'subject', 'teacher', 'teacher.staffPicture', 'room', 'setting.schoolclass', 'setting.schoolclass.armRelation'])
            ->get();

        $emptySummary = [
            'total_staff'           => 0,
            'total_periods'         => 0,
            'avg_periods_per_staff' => 0,
            'busiest_staff'         => null,
            'most_conflicted_staff' => null,
        ];

        if ($slots->isEmpty()) {
            return ['staff' => [], 'summary' => $emptySummary];
        }

        // Which individual slot IDs sit inside a genuine (non-combined)
        // double-booking, grouped by teacher+day+clock-time.
        $conflictSlotIds = [];
        $grouped = $slots->filter(fn($s) => $s->period)
            ->groupBy(fn($s) => $s->teacher_id . '|' . $s->day . '|' . $this->periodTimeSignature($s->period));

        foreach ($grouped as $group) {
            if ($group->count() < 2) continue;
            $arr = $group->values();
            for ($i = 0; $i < $arr->count(); $i++) {
                for ($j = $i + 1; $j < $arr->count(); $j++) {
                    if (!$this->isCombinedSession($arr[$i], $arr[$j])) {
                        $conflictSlotIds[$arr[$i]->id] = true;
                        $conflictSlotIds[$arr[$j]->id] = true;
                    }
                }
            }
        }

        $staff = [];

        foreach ($slots->groupBy('teacher_id') as $teacherId => $teacherSlots) {
            $teacher = $teacherSlots->first()->teacher;
            if (!$teacher) continue;

            $classes   = [];
            $subjects  = [];
            $rooms     = [];
            $dailyLoad = array_fill_keys(self::DAYS, 0);
            $conflictCount = 0;

            foreach ($teacherSlots as $slot) {
                $className = $this->getClassName($slot->setting?->schoolclass);
                $classes[$className] = ($classes[$className] ?? 0) + 1;

                $subjectName = $slot->subject?->subject ?? 'Unknown';
                $subjects[$subjectName] = ($subjects[$subjectName] ?? 0) + 1;

                if ($slot->room) {
                    $roomName = $slot->room->room_name;
                    $rooms[$roomName] = ($rooms[$roomName] ?? 0) + 1;
                }

                if (isset($dailyLoad[$slot->day])) $dailyLoad[$slot->day]++;
                if (isset($conflictSlotIds[$slot->id])) $conflictCount++;
            }

            arsort($classes);
            arsort($subjects);
            arsort($rooms);

            $maxLoad    = max($dailyLoad);
            $busiestDay = $maxLoad > 0 ? array_search($maxLoad, $dailyLoad) : null;

            $staff[] = [
                'id'             => (int) $teacherId,
                'name'           => $teacher->name,
                'email'          => $teacher->email,
                'picture'        => $teacher->staffPicture
                    ? asset('storage/staff_avatars/' . $teacher->staffPicture->picture)
                    : asset('storage/staff_avatars/default.png'),
                'total_periods'  => $teacherSlots->count(),
                'class_count'    => count($classes),
                'subject_count'  => count($subjects),
                'classes'        => $classes,
                'subjects'       => $subjects,
                'rooms'          => $rooms,
                'daily_load'     => $dailyLoad,
                'busiest_day'    => $busiestDay,
                'conflict_count' => $conflictCount,
            ];
        }

        usort($staff, fn($a, $b) => $b['total_periods'] <=> $a['total_periods']);

        $totalStaff   = count($staff);
        $totalPeriods = array_sum(array_column($staff, 'total_periods'));
        $busiest      = $staff[0] ?? null;
        $mostConflicted = collect($staff)->sortByDesc('conflict_count')->first();

        return [
            'staff'   => $staff,
            'summary' => [
                'total_staff'           => $totalStaff,
                'total_periods'         => $totalPeriods,
                'avg_periods_per_staff' => $totalStaff > 0 ? round($totalPeriods / $totalStaff, 1) : 0,
                'busiest_staff'         => $busiest ? ['name' => $busiest['name'], 'periods' => $busiest['total_periods']] : null,
                'most_conflicted_staff' => ($mostConflicted && $mostConflicted['conflict_count'] > 0)
                    ? ['name' => $mostConflicted['name'], 'conflicts' => $mostConflicted['conflict_count']]
                    : null,
            ],
        ];
    }

    private function buildClassStats(array $grid, $periods, array $days, array $dayMeta): array
    {
        $totalSlots = 0; $filled = 0;
        $subjects = []; $teachers = []; $rooms = [];

        foreach ($days as $day) {
            foreach ($periods as $period) {
                $meta = $dayMeta[$day][$period->id] ?? null;
                if (!$meta || $meta['effective_type'] !== 'lesson' || !$meta['applicable']) continue;

                $totalSlots++;
                $slot = $grid[$period->id][$day] ?? null;
                if ($slot && !($slot['is_free'] ?? true)) {
                    $filled++;
                    if (!empty($slot['subject']) && $slot['subject'] !== '—') $subjects[$slot['subject']] = true;
                    if (!empty($slot['teacher']))                             $teachers[$slot['teacher']] = true;
                    if (!empty($slot['room']))                                $rooms[$slot['room']]       = true;
                }
            }
        }

        return [
            'total_slots'   => $totalSlots,
            'filled_slots'  => $filled,
            'free_slots'    => max(0, $totalSlots - $filled),
            'fill_rate'     => $totalSlots > 0 ? (int) round(($filled / $totalSlots) * 100) : 0,
            'subject_count' => count($subjects),
            'teacher_count' => count($teachers),
            'room_count'    => count($rooms),
            'teacher_names' => array_keys($teachers),
        ];
    }

    private function buildWholeSchoolExportData($sessionId, $termId): array
    {
        $settings = TimetableSetting::with(['session', 'term', 'periods'])
            ->join('schoolclass', 'schoolclass.id', '=', 'timetable_settings.schoolclass_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['timetable_settings.*', 'schoolclass.schoolclass as _class_name', 'schoolarm.arm as _arm_name'])
            ->where('timetable_settings.session_id', $sessionId)
            ->when($termId, fn($q) => $q->where('timetable_settings.term_id', $termId))
            ->where('timetable_settings.is_active', true)
            ->orderBy('schoolclass.schoolclass')->orderBy('schoolarm.arm')->get();

        $schoolInfo = SchoolInformation::getActiveSchool();
        $session    = Schoolsession::find($sessionId);
        $term       = $termId ? Schoolterm::find($termId) : null;

        if ($settings->isEmpty()) {
            return [[], $schoolInfo, $session, $term, [], ['staff' => [], 'summary' => []]];
        }

        $allTimetables = [];

        foreach ($settings as $setting) {
            $className = trim(($setting->_class_name ?? '') . ' ' . ($setting->_arm_name ?? '')) ?: 'Unknown Class';
            $slots     = TimetableSlot::where('setting_id', $setting->id)
                ->with(['subject', 'teacher', 'period', 'room'])->get();

            $grid = [];
            foreach ($slots as $slot) {
                $grid[$slot->period_id][$slot->day] = [
                    'subject'    => $slot->subject?->subject ?? ($slot->is_free ? 'FREE' : '—'),
                    'teacher'    => $slot->teacher?->name ?? '',
                    'teacher_id' => $slot->teacher_id,
                    'room'       => ($slot->room_id && $slot->room) ? $slot->room->room_name : '',
                    'is_free'    => $slot->is_free ?? !$slot->subject_id,
                ];
            }

            $days    = $setting->active_days ?? self::DAYS;
            $dayMeta = $this->computeDayPeriodMeta($setting);

            $allTimetables[] = [
                'setting_id' => $setting->id,
                'class_name' => $className,
                'periods'    => $setting->periods,
                'grid'       => $grid,
                'days'       => $days,
                'day_meta'   => $dayMeta,
                'stats'      => $this->buildClassStats($grid, $setting->periods, $days, $dayMeta),
            ];
        }

        $overallStats = [
            'total_classes'   => count($allTimetables),
            'total_teachers'  => collect($allTimetables)->pluck('stats.teacher_names')->flatten()->filter()->unique()->count(),
            'avg_fill_rate'   => (int) round(collect($allTimetables)->avg(fn($t) => $t['stats']['fill_rate'])),
            'total_conflicts' => $this->countConflictsForScope((int) $sessionId, $termId ? (int) $termId : null)['total'],
        ];

        $staffAnalytics = $this->buildStaffAnalytics((int) $sessionId, $termId ? (int) $termId : null);

        return [$allTimetables, $schoolInfo, $session, $term, $overallStats, $staffAnalytics];
    }

    private function buildMergedGridData($sessionId, $termId): array
    {
        $settings = TimetableSetting::with(['periods'])
            ->join('schoolclass', 'schoolclass.id', '=', 'timetable_settings.schoolclass_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['timetable_settings.*', 'schoolclass.schoolclass as _class_name', 'schoolarm.arm as _arm_name'])
            ->where('timetable_settings.session_id', $sessionId)
            ->when($termId, fn($q) => $q->where('timetable_settings.term_id', $termId))
            ->where('timetable_settings.is_active', true)
            ->orderBy('schoolclass.schoolclass')->orderBy('schoolarm.arm')
            ->get();

        $schoolInfo = SchoolInformation::getActiveSchool();
        $session    = Schoolsession::find($sessionId);
        $term       = $termId ? Schoolterm::find($termId) : null;

        if ($settings->isEmpty()) {
            return ['rows' => [], 'days' => [], 'classColors' => [], 'classList' => [],
                    'schoolInfo' => $schoolInfo, 'sessionName' => $session->session ?? 'Session',
                    'termName' => $term?->term ?? 'All Terms', 'generatedAt' => now()->format('d M Y, H:i'),
                    'staffAnalytics' => ['staff' => [], 'summary' => []]];
        }

        $classColorPalette = ['#3B82F6','#8B5CF6','#10B981','#F59E0B','#EF4444','#06B6D4','#F97316','#EC4899','#14B8A6','#84CC16','#6366F1','#D946EF'];

        $classData    = [];
        $classColors  = [];
        $colorIdx     = 0;
        $allDaysUnion = [];
        $timeSlotMap  = [];

        foreach ($settings as $setting) {
            $className = trim(($setting->_class_name ?? '') . ' ' . ($setting->_arm_name ?? '')) ?: 'Unknown Class';
            $classColors[$className] = $classColorPalette[$colorIdx++ % count($classColorPalette)];

            $slots = TimetableSlot::where('setting_id', $setting->id)->with(['subject', 'teacher', 'room'])->get();
            $grid  = [];
            foreach ($slots as $slot) {
                $grid[$slot->period_id][$slot->day] = [
                    'subject'    => $slot->subject?->subject,
                    'teacher'    => $slot->teacher?->name,
                    'teacher_id' => $slot->teacher_id,
                    'room'       => $slot->room?->room_name,
                    'is_free'    => $slot->is_free ?? !$slot->subject_id,
                ];
            }

            $days    = $setting->active_days ?? self::DAYS;
            $allDaysUnion = array_unique(array_merge($allDaysUnion, $days));
            $dayMeta = $this->computeDayPeriodMeta($setting);

            foreach ($setting->periods as $period) {
                $key = substr($period->start_time, 0, 5) . '-' . substr($period->end_time, 0, 5);
                if (!isset($timeSlotMap[$key])) {
                    $timeSlotMap[$key] = [
                        'start' => substr($period->start_time, 0, 5),
                        'end'   => substr($period->end_time, 0, 5),
                        'names' => [],
                    ];
                }
                $timeSlotMap[$key]['names'][] = $period->name;
            }

            $classData[$className] = ['grid' => $grid, 'days' => $days, 'dayMeta' => $dayMeta, 'periods' => $setting->periods];
        }

        $dayOrder = self::DAYS;
        usort($allDaysUnion, fn($a, $b) => array_search($a, $dayOrder) <=> array_search($b, $dayOrder));
        uasort($timeSlotMap, fn($a, $b) => strcmp($a['start'], $b['start']));

        $mergedRows = [];
        foreach ($timeSlotMap as $info) {
            $label = collect($info['names'])->countBy()->sortDesc()->keys()->first() ?? 'Period';

            $rowEntries = [];
            foreach ($allDaysUnion as $day) {
                $entries = []; $anyBreak = false; $applicable = false;

                foreach ($classData as $className => $cd) {
                    if (!in_array($day, $cd['days'])) continue;
                    $matchedPeriod = $cd['periods']->first(fn($p) =>
                        substr($p->start_time, 0, 5) === $info['start'] && substr($p->end_time, 0, 5) === $info['end']
                    );
                    if (!$matchedPeriod) continue;

                    $meta = $cd['dayMeta'][$day][$matchedPeriod->id] ?? null;
                    if (!$meta || !$meta['applicable']) continue;
                    $applicable = true;

                    if ($meta['effective_type'] !== 'lesson') { $anyBreak = true; continue; }

                    $slotInfo = $cd['grid'][$matchedPeriod->id][$day] ?? null;
                    if (!$slotInfo || $slotInfo['is_free']) continue;

                    $entries[] = [
                        'class'       => $className,
                        'subject'     => $slotInfo['subject'] ?? '—',
                        'teacher'     => $slotInfo['teacher'] ?? '',
                        'teacher_id'  => $slotInfo['teacher_id'] ?? null,
                        'room'        => $slotInfo['room'] ?? '',
                        'color'       => $classColors[$className],
                        'is_conflict' => false,
                    ];
                }

                // Mark a genuine teacher double-booking within this cell —
                // same teacher appearing under two different classes at the
                // same day+time. Drives the red conflict indicator and the
                // staff-conflict count shown when that teacher is selected.
                $byTeacherInCell = collect($entries)->filter(fn($e) => $e['teacher_id'])->groupBy('teacher_id');
                foreach ($byTeacherInCell as $tId => $group) {
                    if ($group->count() > 1) {
                        foreach ($entries as &$entryRef) {
                            if ($entryRef['teacher_id'] == $tId) $entryRef['is_conflict'] = true;
                        }
                        unset($entryRef);
                    }
                }

                $rowEntries[$day] = [
                    'entries'     => $entries,
                    'is_break'    => $anyBreak && empty($entries),
                    'applicable'  => $applicable,
                ];
            }

            $mergedRows[] = ['label' => $label, 'time' => $info['start'] . ' – ' . $info['end'], 'days' => $rowEntries];
        }

        return [
            'rows'           => $mergedRows,
            'days'           => $allDaysUnion,
            'classColors'    => $classColors,
            'classList'      => array_keys($classColors),
            'schoolInfo'     => $schoolInfo,
            'sessionName'    => $session->session ?? 'Session',
            'termName'       => $term?->term ?? 'All Terms',
            'generatedAt'    => now()->format('d M Y, H:i'),
            'dayColors'      => self::DAY_COLORS,
            'staffAnalytics' => $this->buildStaffAnalytics((int) $sessionId, $termId ? (int) $termId : null),
        ];
    }

    // =========================================================================
    // EXPORT WHOLE SCHOOL TIMETABLE — PDF
    // =========================================================================
    public function exportWholeSchool(Request $request)
    {
        $sessionId   = $request->input('session_id');
        $termId      = $request->input('term_id');
        $orientation = $request->input('orientation', 'horizontal');

        if (!$sessionId) return response()->json(['error' => 'Session is required'], 400);

        [$allTimetables, $schoolInfo, $session, $term, $overallStats] = $this->buildWholeSchoolExportData($sessionId, $termId);

        if (empty($allTimetables)) return response()->json(['error' => 'No timetables found'], 404);

        return $this->exportWholeSchoolPdf($allTimetables, $schoolInfo, $session, $term, $orientation, $overallStats);
    }

    // =========================================================================
    // EXPORT WHOLE SCHOOL TIMETABLE — WEB VIEW
    // =========================================================================
    public function exportWholeSchoolWeb(Request $request)
    {
        $sessionId = $request->input('session_id');
        $termId    = $request->input('term_id');

        if (!$sessionId) return response()->json(['error' => 'Session is required'], 400);

        [$allTimetables, $schoolInfo, $session, $term, $overallStats, $staffAnalytics] = $this->buildWholeSchoolExportData($sessionId, $termId);

        if (empty($allTimetables)) {
            abort(404, 'No timetables found for this session/term.');
        }

        $pagetitle = 'Whole School Timetable';

        return view('timetable.exports.whole-school-web', array_merge(
            compact('allTimetables', 'schoolInfo', 'session', 'term', 'overallStats', 'pagetitle', 'staffAnalytics'),
            [
                'sessionName'   => $session->session ?? 'Session',
                'termName'      => $term?->term ?? 'All Terms',
                'dayColors'     => self::DAY_COLORS,
                'generatedAt'   => now()->format('d M Y, H:i'),
            ]
        ));
    }

    // =========================================================================
    // MERGED GRID — PDF
    // =========================================================================
    public function exportMergedGrid(Request $request)
    {
        $sessionId = $request->input('session_id');
        $termId    = $request->input('term_id');
        if (!$sessionId) return response()->json(['error' => 'Session is required'], 400);

        $data = $this->buildMergedGridData($sessionId, $termId);
        if (empty($data['rows'])) return response()->json(['error' => 'No timetables found'], 404);

        $pdf = Pdf::loadView('timetable.exports.merged-grid', $data)->setPaper('a3', 'landscape');
        $filename = 'merged-timetable-' . str_replace([' ', '/'], '-', $data['sessionName']) . '.pdf';
        return $pdf->stream($filename);
    }

    // =========================================================================
    // MERGED GRID — WEB VIEW
    // =========================================================================
    public function mergedGridWeb(Request $request)
    {
        $sessionId = $request->input('session_id');
        $termId    = $request->input('term_id');
        if (!$sessionId) return response()->json(['error' => 'Session is required'], 400);

        $data = $this->buildMergedGridData($sessionId, $termId);
        if (empty($data['rows'])) abort(404, 'No timetables found for this session/term.');

        $pagetitle = 'Merged Timetable';

        return view('timetable.exports.merged-grid-web', array_merge($data, compact('pagetitle')));
    }

    // =========================================================================
    // NOTIFICATIONS / PUBLISH / SUBSTITUTE / AVAILABILITY
    // =========================================================================
    public function sendNotifications(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id' => 'required|exists:timetable_settings,id',
            'type'       => 'required|in:daily_summary,weekly_preview,change_alert',
        ]);

        $setting = TimetableSetting::with([
            'slots.teacher', 'slots.teacher.staffPicture',
            'slots.subject', 'slots.period', 'slots.room',
            'schoolclass', 'session', 'term',
        ])->findOrFail($validated['setting_id']);

        if ($validated['type'] === 'daily_summary') {
            $todayHoliday = $this->getHolidayForDate(now(), $setting->session_id, $setting->term_id);
            if ($todayHoliday && $todayHoliday->is_full_day) {
                return response()->json(['success' => true, 'message' => "Skipped — today is a holiday ({$todayHoliday->title}).", 'sent' => 0]);
            }
        }

        $sent = $this->dispatchNotifications($setting, $validated['type']);

        return response()->json(['success' => true, 'message' => "Notifications sent to {$sent} teacher(s).", 'sent' => $sent]);
    }

    public function publishSetting(int $settingId): JsonResponse
    {
        $setting = TimetableSetting::findOrFail($settingId);
        $setting->update(['is_published' => true, 'published_at' => now(), 'published_by' => Auth::id()]);
        $this->logTimetableChange(Auth::id(), 'update', 'TimetableSetting', $setting->id, null, ['is_published' => true]);

        return response()->json(['success' => true, 'message' => 'Timetable published and locked.']);
    }

    public function unpublishSetting(int $settingId): JsonResponse
    {
        $setting = TimetableSetting::findOrFail($settingId);
        $setting->update(['is_published' => false, 'published_at' => null, 'published_by' => null]);
        $this->logTimetableChange(Auth::id(), 'update', 'TimetableSetting', $setting->id, null, ['is_published' => false]);

        return response()->json(['success' => true, 'message' => 'Timetable unpublished. It can now be edited.']);
    }

    public function publishAndNotify(Request $request): JsonResponse
    {
        $validated = $request->validate(['setting_id' => 'required|exists:timetable_settings,id']);

        $setting = TimetableSetting::with([
            'slots.teacher', 'slots.teacher.staffPicture',
            'slots.subject', 'slots.period', 'slots.room',
            'schoolclass', 'session', 'term',
        ])->findOrFail($validated['setting_id']);

        $setting->update(['is_published' => true, 'published_at' => now(), 'published_by' => Auth::id()]);
        $this->logTimetableChange(Auth::id(), 'update', 'TimetableSetting', $setting->id, null, ['is_published' => true]);

        $sent = $this->dispatchNotifications($setting, 'weekly_preview');

        return response()->json([
            'success' => true,
            'message' => "Timetable published and notifications sent to {$sent} teacher(s).",
            'sent'    => $sent,
        ]);
    }

    // =========================================================================
    // PUBLISH + SAVE NAMED SNAPSHOT
    // "Lock it in" once the admin is satisfied with a generated result.
    // Publishes the LIVE setting as before (so it behaves exactly like
    // publishSetting for anyone still viewing/editing that class), AND
    // deep-replicates it into a separate, frozen, named row (is_snapshot =
    // true) via replicateSettingDeep(). The live setting can keep being
    // regenerated or edited later — the snapshot never changes and stays
    // retrievable under its name via getSavedTimetables()/getGrid().
    // =========================================================================
    public function publishAndSaveSnapshot(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id' => 'required|exists:timetable_settings,id',
            'name'       => 'required|string|min:2|max:150',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $setting = TimetableSetting::with(['periods', 'constraints', 'slots'])->findOrFail($validated['setting_id']);

        DB::beginTransaction();
        try {
            $setting->update(['is_published' => true, 'published_at' => now(), 'published_by' => Auth::id()]);
            $this->logTimetableChange(Auth::id(), 'update', 'TimetableSetting', $setting->id, null, ['is_published' => true]);

            $result   = $this->replicateSettingDeep($setting, [
                'is_published'           => true,
                'published_at'           => now(),
                'published_by'           => Auth::id(),
                'is_snapshot'            => true,
                'snapshot_of_setting_id' => $setting->id,
                'generation_name'        => $validated['name'],
                'generation_notes'       => $validated['notes'] ?? $setting->generation_notes,
                'generation_seed'        => $setting->generation_seed,
                'generated_by'           => $setting->generated_by ?? Auth::id(),
                'generated_at'           => $setting->generated_at ?? now(),
            ]);
            $snapshot = $result['setting'];

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => "Saved and locked as \"{$validated['name']}\".",
                'snapshot_id' => $snapshot->id,
                'setting_id'  => $setting->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('publishAndSaveSnapshot failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // SAVED TIMETABLES — list of named, locked snapshots (see
    // publishAndSaveSnapshot above). Each one can be opened read-only via
    // getGrid($snapshotId) / getSetting($snapshotId) — a snapshot is just a
    // TimetableSetting row like any other, so those endpoints work on it
    // unchanged.
    // =========================================================================
    public function getSavedTimetables(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id'     => 'nullable|exists:schoolsession,id',
            'term_id'        => 'nullable|exists:schoolterm,id',
            'schoolclass_id' => 'nullable|exists:schoolclass,id',
        ]);

        $snapshots = TimetableSetting::with(['session', 'term', 'generator'])
            ->where('is_snapshot', true)
            ->when($validated['session_id'] ?? null, fn($q, $v) => $q->where('session_id', $v))
            ->when($validated['term_id'] ?? null, fn($q, $v) => $q->where('term_id', $v))
            ->when($validated['schoolclass_id'] ?? null, fn($q, $v) => $q->where('schoolclass_id', $v))
            ->orderByDesc('generated_at')
            ->get();

        $classIds = $snapshots->pluck('schoolclass_id')->unique()->filter();
        $classes  = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
            ->whereIn('schoolclass.id', $classIds)->get()->keyBy('id');

        $list = $snapshots->map(function ($s) use ($classes) {
            return [
                'id'                => $s->id,
                'name'              => $s->generation_name,
                'notes'             => $s->generation_notes,
                'class_name'        => $this->getClassName($classes[$s->schoolclass_id] ?? null),
                'session'           => $s->session?->session,
                'term'              => $s->term?->term,
                'generated_by'      => $s->generator?->name,
                'generated_at'      => optional($s->generated_at)->format('d M Y, H:i'),
                'seed'              => $s->generation_seed,
                'source_setting_id' => $s->snapshot_of_setting_id,
            ];
        });

        return response()->json(['success' => true, 'saved_timetables' => $list]);
    }

    private function dispatchNotifications(TimetableSetting $setting, string $type): int
    {
        $byTeacher = $setting->slots->whereNotNull('teacher_id')->groupBy('teacher_id');
        $sent = 0;

        foreach ($byTeacher as $teacherId => $teacherSlots) {
            $teacher = $teacherSlots->first()->teacher;
            if (!$teacher || !$teacher->email) continue;

            $notifData = [
                'teacher'         => $teacher->name,
                'teacher_picture' => $teacher->staffPicture ? asset('storage/staff_avatars/' . $teacher->staffPicture->picture) : null,
                'class'           => $this->getClassName($setting->schoolclass),
                'session'         => $setting->session->session ?? '',
                'term'            => $setting->term?->term ?? '',
                'slots'           => $teacherSlots->map(fn($s) => [
                    'day'     => $s->day,
                    'period'  => $s->period?->name,
                    'time'    => $this->formatTime($s->period?->start_time ?? '') . ' – ' . $this->formatTime($s->period?->end_time ?? ''),
                    'subject' => $s->subject?->subject,
                    'room'    => $s->room?->room_name,
                ])->toArray(),
                'type'      => $type,
                'generated' => now()->format('d M Y H:i'),
            ];

            try {
                Mail::to($teacher->email)->send(new TimetableNotificationMail($notifData));
                foreach ($teacherSlots as $slot) {
                    TimetableNotification::create([
                        'teacher_id'   => $teacherId, 'slot_id'    => $slot->id,
                        'type'         => $type, 'email' => $teacher->email,
                        'scheduled_at' => now(), 'sent_at'   => now(),
                        'status'       => 'sent', 'payload'   => json_encode($notifData),
                    ]);
                }
                $sent++;
            } catch (\Exception $e) {
                Log::error('Timetable notification failed', ['teacher_id' => $teacherId, 'error' => $e->getMessage()]);
            }
        }

        return $sent;
    }

    public function deleteSetting(Request $request, int $settingId): JsonResponse
    {
        $setting = TimetableSetting::findOrFail($settingId);

        if ($conflict = $this->versionConflictResponse($setting, $request->input('expected_updated_at'))) {
            return $conflict;
        }

        try { $this->logTimetableChange(Auth::id(), 'delete', 'TimetableSetting', $settingId, null, $setting->toArray()); }
        catch (\Exception $e) { Log::warning('Audit log failed: ' . $e->getMessage()); }
        $setting->delete();
        return response()->json(['success' => true]);
    }

    // =========================================================================
    // PRIVATE: Deep-replicate a setting — periods, constraints, and slots —
    // into a new TimetableSetting row. Shared by cloneSetting() (editable
    // copy into a different session/term) and saveGenerationSnapshot()
    // (frozen, named, historical copy of the live setting). $overrides is
    // applied to the new setting after the base replicate() so callers can
    // set is_published, is_snapshot, generation_name, etc. without this
    // helper needing to know about every possible use case.
    // =========================================================================
    private function replicateSettingDeep(TimetableSetting $source, array $overrides = []): array
    {
        $new = $source->replicate();
        $new->is_published = false;
        $new->published_at = null;
        $new->published_by = null;
        $new->editing_by   = null;
        $new->editing_at   = null;
        $new->created_by   = Auth::id();
        $new->updated_by   = Auth::id();
        foreach ($overrides as $key => $value) {
            $new->{$key} = $value;
        }
        $new->save();

        $periodMap = [];
        foreach ($source->periods as $period) {
            $newPeriod             = $period->replicate();
            $newPeriod->setting_id = $new->id;
            $newPeriod->save();
            $periodMap[$period->id] = $newPeriod->id;
        }

        foreach ($source->constraints as $constraint) {
            $newC = $constraint->replicate();
            $newC->setting_id = $new->id;
            $newC->save();
        }

        $slotIds = [];
        foreach ($source->slots as $slot) {
            if (!isset($periodMap[$slot->period_id])) continue;
            $newSlot = $slot->replicate();
            $newSlot->setting_id = $new->id;
            $newSlot->period_id  = $periodMap[$slot->period_id];
            $newSlot->save();
            $slotIds[] = $newSlot->id;
        }

        return ['setting' => $new, 'slot_ids' => $slotIds];
    }

    public function cloneSetting(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id'     => 'required|exists:timetable_settings,id',
            'new_session_id' => 'nullable|exists:schoolsession,id',
            'new_term_id'    => 'nullable|exists:schoolterm,id',
            'force'          => 'boolean',
        ]);

        $oldSetting = TimetableSetting::with(['periods', 'constraints', 'slots', 'editor'])->findOrFail($validated['setting_id']);

        if (empty($validated['force'])
            && $editingWarning = $this->editingRecentlyResponse($oldSetting, 'Clone anyway?')) {
            return $editingWarning;
        }

        DB::beginTransaction();
        try {
            $result     = $this->replicateSettingDeep($oldSetting, [
                'session_id' => $validated['new_session_id'] ?? $oldSetting->session_id,
                'term_id'    => $validated['new_term_id'] ?? $oldSetting->term_id,
            ]);
            $newSetting = $result['setting'];
            $newSlotIds = $result['slot_ids'];

            $conflicts = $this->detectTeacherConflictsForSetting($newSetting, $newSlotIds);

            DB::commit();
            return response()->json([
                'success'       => true,
                'setting_id'    => $newSetting->id,
                'conflicts'     => $conflicts,
                'has_conflicts' => count($conflicts) > 0,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // PRIVATE: Detect teacher conflicts for a freshly cloned setting.
    // Grouped by CLOCK TIME, not period_id — the clone gets brand-new
    // TimetablePeriod rows (via $periodMap), so its slots' period_id values
    // never coincide with any other setting's rows even at the identical
    // wall-clock time. period_id-based grouping here effectively never
    // fired.
    // =========================================================================
    private function detectTeacherConflictsForSetting(TimetableSetting $setting, array $slotIds): array
    {
        if (empty($slotIds)) return [];

        $newSlots = TimetableSlot::whereIn('id', $slotIds)
            ->whereNotNull('teacher_id')->where('is_free', false)
            ->with(['period', 'subject', 'teacher'])->get();

        if ($newSlots->isEmpty()) return [];

        $sessionId = $setting->session_id;
        $termId    = $setting->term_id;

        $others = TimetableSlot::whereHas('setting', function ($q) use ($sessionId, $termId, $setting) {
                $q->where('session_id', $sessionId)->where('is_active', true)->where('id', '!=', $setting->id);
                if ($termId) $q->where('term_id', $termId);
                else         $q->whereNull('term_id');
            })
            ->whereIn('teacher_id', $newSlots->pluck('teacher_id')->unique())
            ->where('is_free', false)->whereNotNull('subject_id')
            ->with(['setting.schoolclass', 'subject', 'teacher', 'period'])
            ->get()
            ->filter(fn($s) => $s->period)
            ->groupBy(fn($s) => $s->teacher_id . '|' . $s->day . '|' . $this->periodTimeSignature($s->period));

        $conflicts = [];
        foreach ($newSlots as $newSlot) {
            if (!$newSlot->period) continue;
            $key = $newSlot->teacher_id . '|' . $newSlot->day . '|' . $this->periodTimeSignature($newSlot->period);
            if (!$others->has($key)) continue;

            $clashing = $others->get($key)->first();
            
            $isCombined = $newSlot->room_id && $clashing->room_id
                && $newSlot->room_id == $clashing->room_id
                && $newSlot->subject_id == $clashing->subject_id;

            if ($isCombined) {
                continue;
            }

            $newSlot->update(['notes' => trim(($newSlot->notes ? $newSlot->notes . "\n" : '')
                . "⚠️ Clone conflict: {$newSlot->teacher?->name} is already teaching in "
                . $this->getClassName($clashing->setting?->schoolclass) . " at this time.")]);

            $conflicts[] = [
                'teacher'             => $newSlot->teacher?->name ?? 'Unknown',
                'day'                 => $newSlot->day,
                'period'              => $newSlot->period?->name,
                'period_time'         => $this->formatTime($newSlot->period?->start_time ?? '') . ' – ' . $this->formatTime($newSlot->period?->end_time ?? ''),
                'subject_here'        => $newSlot->subject?->subject,
                'conflicting_class'   => $this->getClassName($clashing->setting?->schoolclass),
                'conflicting_subject' => $clashing->subject?->subject,
            ];
        }

        return $conflicts;
    }

    public function bulkUpdateSlots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id'           => 'required|exists:timetable_settings,id',
            'updates'              => 'required|array',
            'updates.*.period_id'  => 'required|exists:timetable_periods,id',
            'updates.*.day'        => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'updates.*.subject_id' => 'nullable|exists:subject,id',
            'updates.*.teacher_id' => 'nullable|exists:users,id',
        ]);

        $setting = TimetableSetting::findOrFail($validated['setting_id']);
        if ($lock = $this->publishedLockResponse($setting)) return $lock;

        DB::beginTransaction();
        try {
            foreach ($validated['updates'] as $update) {
                TimetableSlot::updateOrCreate(
                    ['setting_id' => $validated['setting_id'], 'period_id' => $update['period_id'], 'day' => $update['day']],
                    ['subject_id' => $update['subject_id'] ?? null, 'teacher_id' => $update['teacher_id'] ?? null, 'is_free' => empty($update['subject_id'])]
                );
            }
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function requestSubstitute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slot_id'               => 'required|exists:timetable_slots,id',
            'substitute_teacher_id' => 'required|exists:users,id',
            'reason'                => 'required|string|max:500',
            'assignment_date'       => 'required|date|after_or_equal:today',
        ]);

        $slot = TimetableSlot::with('setting')->findOrFail($validated['slot_id']);
        if ($slot->teacher_id != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You can only request substitutes for your own classes'], 403);
        }

        $assignmentDate = Carbon::parse($validated['assignment_date']);
        $holiday = $this->getHolidayForDate($assignmentDate, $slot->setting?->session_id, $slot->setting?->term_id);
        if ($holiday && $holiday->is_full_day) {
            return response()->json([
                'success' => false,
                'message' => "That date ({$assignmentDate->format('d M Y')}) is a holiday — {$holiday->title}. No substitute needed.",
            ], 422);
        }

        $substitute = SubstituteAssignment::create([
            'original_teacher_id'   => Auth::id(),
            'substitute_teacher_id' => $validated['substitute_teacher_id'],
            'slot_id'               => $slot->id,
            'assignment_date'       => $validated['assignment_date'],
            'reason'                => $validated['reason'],
            'status'                => 'pending',
        ]);

        return response()->json(['success' => true, 'substitute' => $substitute]);
    }

    public function approveSubstitute(Request $request, int $substituteId): JsonResponse
    {
        $substitute = SubstituteAssignment::findOrFail($substituteId);
        if (!Auth::user()->can('Approve substitute')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $substitute->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);
        $slot         = $substitute->slot;
        $originalName = $substitute->originalTeacher->name ?? 'Unknown';
        $slot->update([
            'teacher_id' => $substitute->substitute_teacher_id,
            'notes'      => ($slot->notes ? $slot->notes . "\n" : '') . "[SUBSTITUTE] Original: {$originalName}, Date: {$substitute->assignment_date}",
        ]);
        return response()->json(['success' => true]);
    }

    public function getSubstituteRequests(Request $request): JsonResponse
    {
        $requests = SubstituteAssignment::with(['originalTeacher', 'substituteTeacher', 'slot.period', 'slot.subject'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date,   fn($q) => $q->whereDate('assignment_date', $request->date))
            ->orderBy('created_at', 'desc')->paginate($request->per_page ?? 20);
        return response()->json(['success' => true, 'requests' => $requests]);
    }

    public function getAvailableSubstitutes(Request $request): JsonResponse
    {
        $substitutes = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))
            ->with('staffPicture')->get()
            ->map(fn($t) => [
                'id'      => $t->id,
                'name'    => $t->name,
                'email'   => $t->email,
                'picture' => $t->staffPicture
                    ? asset('storage/staff_avatars/' . $t->staffPicture->picture)
                    : asset('storage/staff_avatars/default.png'),
            ]);
        return response()->json(['success' => true, 'substitutes' => $substitutes]);
    }

    public function saveTeacherAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id'                  => 'required|exists:users,id',
            'availability'                => 'required|array',
            'availability.*.day'          => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'availability.*.start_time'   => 'required|date_format:H:i',
            'availability.*.end_time'     => 'required|date_format:H:i|after:start_time',
            'availability.*.is_available' => 'boolean',
        ]);
        foreach ($validated['availability'] as $avail) {
            TeacherAvailability::updateOrCreate(
                ['teacher_id' => $validated['teacher_id'], 'day' => $avail['day']],
                ['start_time' => $avail['start_time'], 'end_time' => $avail['end_time'], 'is_available' => $avail['is_available'] ?? true]
            );
        }
        return response()->json(['success' => true]);
    }

    public function getTeacherAvailability(int $teacherId): JsonResponse
    {
        return response()->json(['success' => true, 'availability' => TeacherAvailability::where('teacher_id', $teacherId)->get()]);
    }

    public function workloadDashboard(Request $request): JsonResponse
    {
        $sessionId = $request->session_id ?? Schoolsession::where('status', 'Current')->value('id');
        $teachers  = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->with('staffPicture')->get();

        $workloadData = [];
        foreach ($teachers as $teacher) {
            $slots = TimetableSlot::where('teacher_id', $teacher->id)
                ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
                ->with(['setting.schoolclass', 'setting.schoolclass.armRelation', 'subject'])->get();

            $dailyLoad = [];
            foreach (self::DAYS as $day) $dailyLoad[$day] = $slots->where('day', $day)->count();

            $workloadData[] = [
                'teacher_id'       => $teacher->id,
                'teacher_name'     => $teacher->name,
                'teacher_picture'  => $teacher->staffPicture ? asset('storage/staff_avatars/' . $teacher->staffPicture->picture) : null,
                'periods_assigned' => $slots->count(),
                'classes_taught'   => $slots->map(fn($s) => $this->getClassName($s->setting?->schoolclass))->filter()->unique()->values(),
                'subjects_taught'  => $slots->pluck('subject.subject')->filter()->unique()->values(),
                'daily_load'       => $dailyLoad,
            ];
        }
        usort($workloadData, fn($a, $b) => $b['periods_assigned'] - $a['periods_assigned']);
        return response()->json(['success' => true, 'workload' => $workloadData]);
    }

    public function getClassSubjects(Request $request): JsonResponse
    {
        $subjectTeachers = SubjectTeacher::where('sessionid', $request->input('session_id'))
            ->when($request->input('term_id'), fn($q) => $q->where('termid', $request->input('term_id')))
            ->whereHas('subjectclass', fn($q) => $q->where('schoolclassid', $request->input('class_id')))
            ->with(['subject', 'staff', 'staff.staffPicture'])->get()
            ->map(fn($st) => [
                'subject_id'      => $st->subjectid,
                'subject_name'    => $st->subject?->subject,
                'subject_code'    => $st->subject?->subject_code,
                'teacher_id'      => $st->staffid,
                'teacher_name'    => $st->staff?->name,
                'teacher_picture' => $st->staff && $st->staff->staffPicture
                    ? asset('storage/staff_avatars/' . $st->staff->staffPicture->picture)
                    : asset('storage/staff_avatars/default.png'),
            ]);
        return response()->json(['success' => true, 'data' => $subjectTeachers]);
    }
}