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

    // How long an "editing_at" heartbeat is considered live before it's treated as stale.
    const EDITING_LOCK_TTL_MINUTES = 3;

    public function __construct()
    {
        $this->middleware('permission:View timetable|Create timetable|Edit timetable|Delete timetable|Generate timetable', ['only' => ['index', 'getSetting', 'getGrid', 'heartbeat', 'releaseEditing']]);
        $this->middleware('permission:Create timetable', ['only' => ['setup', 'saveSettings']]);
        $this->middleware('permission:Edit timetable', ['only' => ['saveSlot', 'bulkUpdateSlots', 'cloneSetting']]);
        $this->middleware('permission:Delete timetable', ['only' => ['deleteSetting']]);
        $this->middleware('permission:Generate timetable', ['only' => ['autoGenerate', 'autoGenerateWholeSchool', 'applyGenerationTemplate', 'getTeacherAssignments', 'assignTeacherToSubjectclass', 'unassignTeacherFromSubjectclass']]);
        $this->middleware('permission:View my timetable', ['only' => ['teacherView']]);
        $this->middleware('permission:Manage timetable settings', ['only' => ['saveSettings', 'rebuildPeriodsFromAnchors', 'saveHalfDays']]);
        $this->middleware('permission:Manage timetable constraints', ['only' => ['saveConstraints']]);
        $this->middleware('permission:View timetable reports', ['only' => ['workloadDashboard', 'generateAnalytics']]);
        $this->middleware('permission:Export timetable', ['only' => ['export', 'exportWholeSchool']]);
        $this->middleware('permission:Request substitute', ['only' => ['requestSubstitute']]);
        $this->middleware('permission:Approve substitute', ['only' => ['approveSubstitute']]);
        $this->middleware('permission:View substitute requests', ['only' => ['getSubstituteRequests']]);
        $this->middleware('permission:Manage teacher availability', ['only' => ['saveTeacherAvailability', 'getTeacherAvailability']]);
        $this->middleware('permission:Check timetable conflicts', ['only' => ['checkConflicts']]);
        $this->middleware('permission:Send timetable notifications', ['only' => ['sendNotifications', 'publishAndNotify']]);
        $this->middleware('permission:Publish timetable', ['only' => ['publishSetting', 'unpublishSetting', 'publishAndNotify']]);
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
        if (!empty($schoolclass->arm_name)) return ' ' . $schoolclass->arm_name;
        if (is_object($schoolclass->arm) && isset($schoolclass->arm->arm)) return ' ' . $schoolclass->arm->arm;
        if (is_string($schoolclass->arm) && !is_numeric($schoolclass->arm)) return ' ' . $schoolclass->arm;
        if (is_numeric($schoolclass->arm)) {
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

        $teacherBusyKeys = TimetableSlot::whereHas('setting', function($q) use ($sessionId, $termId) {
                $q->where('session_id', $sessionId)->where('is_active', true);
                if ($termId) $q->where('term_id', $termId);
                else         $q->whereNull('term_id');
            })
            ->where('teacher_id', $teacherId)
            ->where('is_free', false)
            ->whereNotNull('subject_id')
            ->get(['day', 'period_id'])
            ->mapWithKeys(fn($s) => [$s->day . '_' . $s->period_id => true]);

        foreach ($days as $day) {
            foreach ($periods as $period) {
                if ($day === $currentDay && $period->id == $currentPeriodId) continue;
                if ($teacherBusyKeys->has($day . '_' . $period->id)) continue;
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
        return Room::where('is_active', true)
            ->when($excludeRoomId, fn($q) => $q->where('id', '!=', $excludeRoomId))
            ->whereNotIn('id', function($q) use ($periodId, $day, $sessionId, $termId) {
                $q->select('room_id')->from('timetable_slots')
                  ->where('period_id', $periodId)->where('day', $day)
                  ->whereNotNull('room_id')->where('is_free', false)
                  ->whereIn('setting_id', function($q2) use ($sessionId, $termId) {
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
    // the client last loaded it. $expectedUpdatedAt is whatever timestamp the
    // client last saw; null means "don't check" (e.g. first-ever save).
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
    // Soft warning only — caller can override with force:true.
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
    // HELPER: For every (day, period) pair, resolve what it actually IS that
    // day.
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
            'max_lessons_per_day'     => 'nullable|integer|min:1',
            'half_days'               => 'nullable|array',
            'half_days.*.day'         => 'required_with:half_days|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'half_days.*.lessons'     => 'required_with:half_days|integer|min:1',
            'deprioritize_break_adjacent' => 'boolean',
        ]);

        $classIds = $validated['schoolclass_ids'] ?? SubjectTeacher::where('sessionid', $validated['session_id'])
            ->whereHas('subjectclass')
            ->with('subjectclass')
            ->get()
            ->pluck('subjectclass.schoolclassid')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

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
                    'free_periods_per_week'        => $validated['free_periods_per_week'] ?? null,
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
        ]);

        $setting   = TimetableSetting::findOrFail($validated['setting_id']);
        $sessionId = $setting->session_id;
        $termId    = $setting->term_id;
        $period    = TimetablePeriod::find($validated['period_id']);

        $conflicts = [];
        $warnings  = [];

        if (!empty($validated['teacher_id'])) {
            $teacherConflict = TimetableSlot::where('period_id', $validated['period_id'])
                ->where('day', $validated['day'])
                ->where('teacher_id', $validated['teacher_id'])
                ->where('setting_id', '!=', $validated['setting_id'])
                ->where('is_free', false)
                ->whereNotNull('subject_id')
                ->whereHas('setting', function ($q) use ($sessionId, $termId) {
                    $q->where('session_id', $sessionId)->where('is_active', true);
                    if ($termId) $q->where('term_id', $termId);
                    else         $q->whereNull('term_id');
                })
                ->with(['setting', 'subject'])
                ->first();

            if ($teacherConflict) {
                $sc = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                    ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
                    ->where('schoolclass.id', $teacherConflict->setting->schoolclass_id)->first();
                $teacherConflict->setting->setRelation('schoolclass', $sc);

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
            $roomConflict = TimetableSlot::where('period_id', $validated['period_id'])
                ->where('day', $validated['day'])
                ->where('room_id', $validated['room_id'])
                ->where('setting_id', '!=', $validated['setting_id'])
                ->where('is_free', false)
                ->whereNotNull('subject_id')
                ->whereHas('setting', function ($q) use ($sessionId, $termId) {
                    $q->where('session_id', $sessionId)->where('is_active', true);
                    if ($termId) $q->where('term_id', $termId);
                    else         $q->whereNull('term_id');
                })
                ->with(['setting', 'subject', 'teacher'])
                ->first();

            if ($roomConflict) {
                $sc = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
                    ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
                    ->where('schoolclass.id', $roomConflict->setting->schoolclass_id)->first();
                $roomConflict->setting->setRelation('schoolclass', $sc);

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

        $currentSchoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
            ->where('schoolclass.id', $currentSetting->schoolclass_id)->first();
        $currentSetting->setRelation('schoolclass', $currentSchoolclass);
        $currentClassName = $this->getClassName($currentSetting->schoolclass);

        if (empty($validated['force_save'])) {

            if (!empty($validated['teacher_id'])) {
                $conflict = TimetableSlot::where('period_id', $validated['period_id'])
                    ->where('day', $validated['day'])
                    ->where('teacher_id', $validated['teacher_id'])
                    ->where('setting_id', '!=', $validated['setting_id'])
                    ->where('is_free', false)
                    ->whereNotNull('subject_id')
                    ->whereHas('setting', function ($q) use ($sessionId, $termId) {
                        $q->where('session_id', $sessionId)->where('is_active', true);
                        if ($termId) $q->where('term_id', $termId);
                        else         $q->whereNull('term_id');
                    })
                    ->with(['setting', 'subject', 'period', 'teacher'])
                    ->first();

                if ($conflict) {
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

            if (!empty($validated['room_id'])) {
                $roomSlotConflict = TimetableSlot::where('period_id', $validated['period_id'])
                    ->where('day', $validated['day'])
                    ->where('room_id', $validated['room_id'])
                    ->where('setting_id', '!=', $validated['setting_id'])
                    ->where('is_free', false)
                    ->whereNotNull('subject_id')
                    ->whereHas('setting', function ($q) use ($sessionId, $termId) {
                        $q->where('session_id', $sessionId)->where('is_active', true);
                        if ($termId) $q->where('term_id', $termId);
                        else         $q->whereNull('term_id');
                    })
                    ->with(['setting', 'subject', 'period', 'teacher'])
                    ->first();

                if ($roomSlotConflict) {
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

                $period = TimetablePeriod::find($validated['period_id']);
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
    // CHECK CONFLICTS
    // =========================================================================
    public function checkConflicts(int $settingId): JsonResponse
    {
        $setting = TimetableSetting::with(['session', 'term'])->findOrFail($settingId);

        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
            ->where('schoolclass.id', $setting->schoolclass_id)->first();
        $setting->setRelation('schoolclass', $schoolclass);

        $sessionId = $setting->session_id;
        $termId    = $setting->term_id;

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

        $teacherGrouped = $slots->groupBy(fn($s) => $s->teacher_id . '|' . $s->day . '|' . $s->period_id);

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

            $alternatives = $this->findAlternativeSlots(
                $first->teacher_id, $first->period_id, $first->day, $setting
            );

            $groupArr = $group->values();
            for ($i = 1; $i < $groupArr->count(); $i++) {
                $other  = $groupArr[$i];
                $classA = $this->getClassName($first->setting?->schoolclass);
                $classB = $this->getClassName($other->setting?->schoolclass);

                $conflicts[] = [
                    'type'                  => $isCrossArm ? 'cross_arm_conflict' : 'teacher_conflict',
                    'conflict_category'     => 'teacher',
                    'day'                   => $first->day,
                    'period'                => $periodName,
                    'period_time'           => $periodTime,
                    'teacher'               => $teacherName,
                    'teacher_id'            => $first->teacher_id,
                    'teacher_picture'       => $first->teacher?->staffPicture
                        ? asset('storage/staff_avatars/' . $first->teacher->staffPicture->picture)
                        : null,
                    'subject_a'             => $first->subject?->subject ?? '—',
                    'subject_b'             => $other->subject?->subject ?? '—',
                    'class_a'               => $classA,
                    'class_b'               => $classB,
                    'is_cross_arm'          => $isCrossArm,
                    'setting_a_id'          => $first->setting_id,
                    'setting_b_id'          => $other->setting_id,
                    'all_classes'           => $classes,
                    'all_subjects'          => $subjects,
                    'alternatives'          => $alternatives,
                    'resolution_suggestion' => $this->buildSuggestionText($teacherName, $classA, $alternatives),
                ];
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

        $roomGrouped = $allSlotsWithRoom->groupBy(fn($s) => $s->room_id . '|' . $s->day . '|' . $s->period_id);

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
            for ($i = 1; $i < $groupArr->count(); $i++) {
                $other  = $groupArr[$i];
                $classA = $this->getClassName($first->setting?->schoolclass);
                $classB = $this->getClassName($other->setting?->schoolclass);

                $conflicts[] = [
                    'type'                  => 'room_conflict',
                    'conflict_category'     => 'room',
                    'day'                   => $first->day,
                    'period'                => $periodName,
                    'period_time'           => $periodTime,
                    'teacher'               => '🏫 ' . ($room?->room_name ?? 'Unknown Room'),
                    'teacher_id'            => null,
                    'teacher_picture'       => null,
                    'subject_a'             => $first->subject?->subject ?? '—',
                    'subject_b'             => $other->subject?->subject ?? '—',
                    'class_a'               => $classA,
                    'class_b'               => $classB,
                    'is_cross_arm'          => false,
                    'setting_a_id'          => $first->setting_id,
                    'setting_b_id'          => $other->setting_id,
                    'all_classes'           => $classes,
                    'all_subjects'          => $subjects,
                    'alternatives'          => [],
                    'resolution_suggestion' => "Room conflict: {$classA} and {$classB} are both assigned to "
                        . ($room?->room_name ?? 'the same room')
                        . " on {$first->day}, {$periodName}. Assign one class to a different room.",
                ];
            }
        }

        return response()->json([
            'success'        => true,
            'conflicts'      => $conflicts,
            'conflict_count' => count($conflicts),
            'has_conflicts'  => count($conflicts) > 0,
            'checked_at'     => now()->format('d M Y, H:i:s'),
        ]);
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
        ]);

        try {
            DB::beginTransaction();

            $setting = TimetableSetting::with(['periods', 'constraints.subject'])->findOrFail($validated['setting_id']);
            if ($lock = $this->publishedLockResponse($setting)) { DB::rollBack(); return $lock; }
            if ($conflict = $this->versionConflictResponse($setting, $validated['expected_updated_at'] ?? null)) { DB::rollBack(); return $conflict; }

            TimetableSlot::where('setting_id', $setting->id)->delete();

            $includeRooms = $validated['include_rooms'] ?? false;

            $crossOccupied = [];
            TimetableSlot::whereHas('setting', function ($q) use ($setting) {
                    $q->where('session_id', $setting->session_id)->where('is_active', true)->where('id', '!=', $setting->id);
                    if ($setting->term_id) $q->where('term_id', $setting->term_id);
                    else                   $q->whereNull('term_id');
                })
                ->whereNotNull('teacher_id')->where('is_free', false)
                ->get(['teacher_id', 'period_id', 'day'])
                ->each(function ($occ) use (&$crossOccupied) {
                    $crossOccupied[$occ->teacher_id][$occ->day][] = $occ->period_id;
                });

            // Cross-class room occupancy — only needed when rooms are being assigned.
            $roomOccupied = [];
            if ($includeRooms) {
                TimetableSlot::whereHas('setting', function ($q) use ($setting) {
                        $q->where('session_id', $setting->session_id)->where('is_active', true)->where('id', '!=', $setting->id);
                        if ($setting->term_id) $q->where('term_id', $setting->term_id);
                        else                   $q->whereNull('term_id');
                    })
                    ->whereNotNull('room_id')->where('is_free', false)
                    ->get(['room_id', 'period_id', 'day'])
                    ->each(function ($occ) use (&$roomOccupied) {
                        $roomOccupied[$occ->room_id][$occ->day][] = $occ->period_id;
                    });
            }

            $stats = $this->runAutoGenerateCore($setting, $crossOccupied, $includeRooms, $roomOccupied);
            $setting->touch();

            DB::commit();
            return response()->json([
                'success'            => true,
                'message'            => 'Timetable generated successfully.',
                'stats'              => $stats,
                'setting_updated_at' => $setting->fresh()->updated_at->toISOString(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
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

        $ordered = $settings->sortByDesc(function ($s) {
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

            $crossOccupied = [];
            $roomOccupied  = [];

            if ($includeRooms) {
                // Seed with room usage from settings OUTSIDE this batch (same session/term,
                // but not being regenerated right now) so we never clash with those either.
                TimetableSlot::whereHas('setting', function ($q) use ($validated, $settings) {
                        $q->where('session_id', $validated['session_id'])->where('is_active', true)
                          ->whereNotIn('id', $settings->pluck('id'));
                        if (!empty($validated['term_id'])) $q->where('term_id', $validated['term_id']);
                        else                                $q->whereNull('term_id');
                    })
                    ->whereNotNull('room_id')->where('is_free', false)
                    ->get(['room_id', 'period_id', 'day'])
                    ->each(function ($occ) use (&$roomOccupied) {
                        $roomOccupied[$occ->room_id][$occ->day][] = $occ->period_id;
                    });
            }

            foreach ($ordered as $setting) {
                $stats = $this->runAutoGenerateCore($setting, $crossOccupied, $includeRooms, $roomOccupied);
                $setting->touch();
                $results[] = [
                    'setting_id'     => $setting->id,
                    'class_name'     => $this->getClassName($setting->schoolclass),
                    'placed'         => $stats['placed'],
                    'unplaced'       => $stats['unplaced_subjects'],
                    'room_shortfall' => $stats['room_shortfall_count'] ?? 0,
                ];
            }

            // ================================================================
            // FIX: Run conflict check after generation
            // ================================================================
            $conflictSummary = $this->countConflictsForScope(
                $validated['session_id'], 
                $validated['term_id'] ?? null
            );

            DB::commit();
            return response()->json([
                'success'          => true,
                'message'          => 'Generated timetables for ' . count($results) . ' class(es).',
                'classes'          => $results,
                'had_shortfalls'   => collect($results)->contains(fn($r) => !empty($r['unplaced'])),
                'include_rooms'    => $includeRooms,
                'conflict_summary' => $conflictSummary, // ← NEW
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('autoGenerateWholeSchool failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // PRIVATE: Fast conflict count across a scope
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
            ->get(['teacher_id', 'day', 'period_id'])
            ->groupBy(fn($s) => $s->teacher_id . '|' . $s->day . '|' . $s->period_id)
            ->filter(fn($g) => $g->count() > 1)
            ->sum(fn($g) => $g->count() - 1);

        $roomConflicts = TimetableSlot::whereHas('setting', $scopeFilter)
            ->whereNotNull('room_id')
            ->where('is_free', false)
            ->whereNotNull('subject_id')
            ->get(['room_id', 'day', 'period_id'])
            ->groupBy(fn($s) => $s->room_id . '|' . $s->day . '|' . $s->period_id)
            ->filter(fn($g) => $g->count() > 1)
            ->sum(fn($g) => $g->count() - 1);

        return [
            'teacher_conflicts' => $teacherConflicts,
            'room_conflicts' => $roomConflicts,
            'total' => $teacherConflicts + $roomConflicts,
        ];
    }

    // =========================================================================
    // PRIVATE: Core slot-placement logic with improved efficiency
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

        // Ensure constraints exist with intelligent defaults
        $constraints = $this->ensureConstraintsExist($setting);

        $dayMeta     = $this->computeDayPeriodMeta($setting);
        $slotPool    = $this->buildWeightedSlotPool($days, $setting, $dayMeta);
        $totalSlots  = count($slotPool);
        $freeTarget  = $setting->free_periods_per_week ?? 0;
        $placementBudget = max(0, $totalSlots - $freeTarget);

        // Get all subject teachers for this class
        $subjectTeachers = SubjectTeacher::where('sessionid', $sessionId)
            ->when($setting->term_id, fn($q) => $q->where('termid', $setting->term_id))
            ->whereHas('subjectclass', fn($q) => $q->where('schoolclassid', $classId))
            ->with(['subject', 'staff'])
            ->get()
            ->groupBy('subjectid');

        $availableRoomIds = $includeRooms
            ? Room::where('is_active', true)->orderBy('room_name')->pluck('id')->toArray()
            : [];

        $availabilityMap = $this->loadTeacherAvailability($subjectTeachers);

        $teacherDaySlot = [];
        $placed = [];
        $unplacedSubjects = [];
        $roomShortfallCount = 0;
        $lessonsPlacedByDay = [];
        $maxPerDay = $setting->max_lessons_per_day ?? null;

        // ================================================================
        // SORT CONSTRAINTS: Subjects with fewer periods get priority
        // (harder to place) — this improves fill rate significantly.
        // ================================================================
        $requirements = $constraints
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

            // Early skip if already placed enough
            $alreadyPlaced = $this->countSubjectPlaced($placed, $subjectId);
            if ($alreadyPlaced >= $needed) continue;

            // Build candidate slots with scoring
            $candidates = [];
            foreach ($slotPool as $slot) {
                $day = $slot['day'];
                $periodId = $slot['period_id'];
                $key = $day . '_' . $periodId;

                // Skip if already used
                if (isset($placed[$key])) continue;

                // Skip if teacher conflict
                if ($teacherId) {
                    if (in_array($periodId, $teacherDaySlot[$teacherId][$day] ?? [])) continue;
                    if (in_array($periodId, $crossOccupied[$teacherId][$day] ?? [])) continue;
                    if (!$this->isTeacherAvailableForPeriod($teacherId, $day, $periodId, $setting, $availabilityMap)) continue;
                }

                // Skip if max lessons per day reached
                if ($maxPerDay && ($lessonsPlacedByDay[$day] ?? 0) >= $maxPerDay) continue;

                $score = 0;
                if (in_array($day, $preferDays)) $score += 20;
                if (in_array($day, $avoidDays)) $score -= 15;
                if ($setting->deprioritize_break_adjacent && !empty($slot['is_break_adjacent'])) $score -= 5;

                // Boost days that currently have fewer lessons (spread load)
                $currentDayLoad = $lessonsPlacedByDay[$day] ?? 0;
                $score -= ($currentDayLoad * 2);

                $candidates[] = [
                    'slot' => $slot,
                    'score' => $score,
                    'key' => $key
                ];
            }

            // Sort by score descending
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
                $key = $candidate['key'];

                // Re-check conditions (might have changed)
                if (isset($placed[$key])) continue;
                if ($maxPerDay && ($lessonsPlacedByDay[$day] ?? 0) >= $maxPerDay) continue;

                // Assign room if needed
                $roomId = $includeRooms
                    ? $this->pickAvailableRoom($availableRoomIds, $day, $periodId, $roomOccupied)
                    : null;
                if ($includeRooms && !$roomId) $roomShortfallCount++;

                // Create the slot
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
                    $crossOccupied[$teacherId][$day][] = $periodId;
                }
                if ($roomId) {
                    $roomOccupied[$roomId][$day][] = $periodId;
                }

                // Try to place a double period (consecutive)
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

                            $teacherConflict = $teacherId && (
                                in_array($nextPeriod->id, $teacherDaySlot[$teacherId][$day] ?? []) ||
                                in_array($nextPeriod->id, $crossOccupied[$teacherId][$day] ?? [])
                            );

                            $teacherAvailableNext = !$teacherId
                                || $this->isTeacherAvailableForPeriod(
                                    $teacherId, $day, $nextPeriod->id, $setting, $availabilityMap
                                );

                            if (!isset($placed[$nextKey]) && !$teacherConflict && $teacherAvailableNext) {
                                $nextRoomId = $roomId;
                                if ($includeRooms && $nextRoomId
                                    && in_array($nextPeriod->id, $roomOccupied[$nextRoomId][$day] ?? [])
                                ) {
                                    $nextRoomId = $this->pickAvailableRoom(
                                        $availableRoomIds, $day, $nextPeriod->id, $roomOccupied
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
                                    $crossOccupied[$teacherId][$day][] = $nextPeriod->id;
                                }
                                if ($nextRoomId) {
                                    $roomOccupied[$nextRoomId][$day][] = $nextPeriod->id;
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

        // Fill remaining empty slots as free periods
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
    // PRIVATE: Ensure a setting has at least one constraint row.
    // If none exist, create sensible defaults from the current SubjectTeacher
    // assignments, DISTRIBUTING PERIODS BASED ON THE ACTUAL TIMETABLE SLOTS.
    // Returns the refreshed constraints collection keyed by subject_id.
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

        // ================================================================
        // CRITICAL FIX: Count actual lesson slots available for this class
        // using the settings the admin specified (periods, active days, half-days)
        // ================================================================
        $dayMeta = $this->computeDayPeriodMeta($setting);
        $totalLessonSlots = 0;
        foreach ($dayMeta as $periodsForDay) {
            foreach ($periodsForDay as $meta) {
                if ($meta['applicable'] && $meta['effective_type'] === 'lesson') {
                    $totalLessonSlots++;
                }
            }
        }

        // Subtract free periods the admin wants to reserve
        $freeTarget = $setting->free_periods_per_week ?? 0;
        $budget = max(0, $totalLessonSlots - $freeTarget);

        // If no slots available, fall back to a reasonable default
        if ($budget === 0) {
            $budget = max(1, count($subjectTeachers) * 2);
        }

        $subjectCount = $subjectTeachers->count();
        
        // Distribute periods evenly across all subjects
        $base = $subjectCount > 0 ? intdiv($budget, $subjectCount) : 0;
        $base = max(1, min($base, 8)); // Cap at 8 periods per subject per week
        $remainder = $budget - ($base * $subjectCount);

        $created = 0;
        foreach ($subjectTeachers->values() as $i => $st) {
            // Skip if constraint already exists (double-check)
            if (TimetableConstraint::where('setting_id', $setting->id)
                    ->where('subject_id', $st->subjectid)
                    ->exists()) {
                continue;
            }

            // Spread the remainder across the first N subjects
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
    // PRIVATE: First free room (from the admin-chosen pool) for a given
    // day/period, tracking usage so no room is double-booked across classes
    // within the same generation run.
    // =========================================================================
    private function pickAvailableRoom(array $availableRoomIds, string $day, int $periodId, array &$roomOccupied): ?int
    {
        foreach ($availableRoomIds as $roomId) {
            if (!in_array($periodId, $roomOccupied[$roomId][$day] ?? [])) {
                return $roomId;
            }
        }
        return null;
    }

    // =========================================================================
    // PRIVATE: Is this teacher free for a given day/period, per their own
    // availability records? Teachers with no availability data on file are
    // treated as available all the time.
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
    // TEACHER ASSIGNMENTS — who teaches what, before generation.
    //
    // NOTE ON DATA MODEL: `Subjectclass` does NOT carry its own session/term.
    // Those live on `SubjectTeacher` (sessionid, termid). A `subjectclass` row
    // only becomes discoverable for a given session/term through the
    // `subjectteacher` row it's linked to via `subjectteacherid`. This mirrors
    // the join pattern already proven in SubjectOperationController — do not
    // "fix" this back to `subjectclass.session` / `subjectclass.termid`,
    // those columns are not the source of truth here.
    //
    // READ-ONLY: this endpoint is a status view only. It shows who is
    // currently teaching what, per the assignments made in Subjects/Classes
    // management, so the admin can see gaps before generating a timetable.
    // It must never write to `subjectclass` or `subjectteacher` — those
    // records are owned exclusively by the Subjects/Classes setup screen and
    // are also used for score entry. Assigning or changing a teacher must be
    // done there, not from the timetable module.
    // =========================================================================
    public function getTeacherAssignments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:schoolsession,id',
            'term_id'    => 'nullable|exists:schoolterm,id',
        ]);

        try {
            // Build the query to get all subject-class assignments for this session/term
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

            // Get student counts for each subjectclass
            $subjectclassIds = $assignments->pluck('subjectclass_id')->unique()->values();
            $studentCounts = SubjectRegistrationStatus::whereIn('subjectclassid', $subjectclassIds)
                ->where('sessionid', $validated['session_id'])
                ->when(!empty($validated['term_id']), fn($q, $termId) => $q->where('termid', $termId))
                ->select(['subjectclassid', DB::raw('COUNT(DISTINCT studentid) as cnt')])
                ->groupBy('subjectclassid')
                ->pluck('cnt', 'subjectclassid');

            // Get teacher pictures
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

            // Group by teacher
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
    // EXPORT CLASS TIMETABLE
    // =========================================================================
    public function export(Request $request, int $settingId)
    {
        $format      = $request->input('format', 'csv');
        $orientation = $request->input('orientation', 'horizontal');

        $setting = TimetableSetting::with(['periods', 'session', 'term'])->findOrFail($settingId);
        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
            ->where('schoolclass.id', $setting->schoolclass_id)->first();
        $setting->setRelation('schoolclass', $schoolclass);

        $slots = TimetableSlot::where('setting_id', $settingId)
            ->with(['subject', 'teacher', 'period', 'room'])->get();

        $dayMeta = $this->computeDayPeriodMeta($setting);

        $grid = []; $subjectColors = []; $colorIdx = 0;
        foreach ($slots as $slot) {
            if ($slot->subject_id && !isset($subjectColors[$slot->subject_id])) {
                $subjectColors[$slot->subject_id] = $colorIdx++ % count(self::SUBJECT_PALETTE);
            }
            $grid[$slot->period_id][$slot->day] = [
                'subject'      => $slot->subject?->subject ?? ($slot->is_free ? 'FREE' : '—'),
                'subject_code' => $slot->subject?->subject_code ?? '',
                'teacher'      => $slot->teacher?->name ?? '',
                'room'         => ($slot->room_id && $slot->room) ? $slot->room->room_name : '',
                'is_free'      => $slot->is_free ?? !$slot->subject_id,
                'subject_id'   => $slot->subject_id,
            ];
        }

        $days        = $setting->active_days ?? self::DAYS;
        $periods     = $setting->periods;
        $className   = $this->getClassName($setting->schoolclass);
        $sessionName = $setting->session->session ?? 'Session';
        $termName    = $setting->term?->term ?? 'All Terms';

        return match($format) {
            'pdf'   => $this->exportPdf($setting, $periods, $days, $grid, $subjectColors, $className, $sessionName, $termName, $orientation, $dayMeta),
            default => $this->exportCsv($setting, $periods, $days, $grid, $className, $sessionName, $dayMeta),
        };
    }

    // =========================================================================
    // EXPORT WHOLE SCHOOL TIMETABLE
    // =========================================================================
    public function exportWholeSchool(Request $request)
    {
        $sessionId   = $request->input('session_id');
        $termId      = $request->input('term_id');
        $orientation = $request->input('orientation', 'horizontal');

        if (!$sessionId) return response()->json(['error' => 'Session is required'], 400);

        $settings = TimetableSetting::with(['session', 'term', 'periods'])
            ->join('schoolclass', 'schoolclass.id', '=', 'timetable_settings.schoolclass_id')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['timetable_settings.*', 'schoolclass.schoolclass as _class_name', 'schoolarm.arm as _arm_name'])
            ->where('timetable_settings.session_id', $sessionId)
            ->when($termId, fn($q) => $q->where('timetable_settings.term_id', $termId))
            ->where('timetable_settings.is_active', true)
            ->orderBy('schoolclass.schoolclass')->orderBy('schoolarm.arm')->get();

        if ($settings->isEmpty()) return response()->json(['error' => 'No timetables found'], 404);

        $schoolInfo    = SchoolInformation::getActiveSchool();
        $allTimetables = [];

        foreach ($settings as $setting) {
            $className = trim(($setting->_class_name ?? '') . ' ' . ($setting->_arm_name ?? '')) ?: 'Unknown Class';
            $slots     = TimetableSlot::where('setting_id', $setting->id)
                ->with(['subject', 'teacher', 'period', 'room'])->get();
            $grid = [];
            foreach ($slots as $slot) {
                $grid[$slot->period_id][$slot->day] = [
                    'subject' => $slot->subject?->subject ?? ($slot->is_free ? 'FREE' : '—'),
                    'teacher' => $slot->teacher?->name ?? '',
                    'room'    => ($slot->room_id && $slot->room) ? $slot->room->room_name : '',
                    'is_free' => $slot->is_free ?? !$slot->subject_id,
                ];
            }
            $allTimetables[] = [
                'class_name' => $className,
                'periods'    => $setting->periods,
                'grid'       => $grid,
                'days'       => $setting->active_days ?? self::DAYS,
                'day_meta'   => $this->computeDayPeriodMeta($setting),
            ];
        }

        $session = Schoolsession::find($sessionId);
        $term    = $termId ? Schoolterm::find($termId) : null;
        return $this->exportWholeSchoolPdf($allTimetables, $schoolInfo, $session, $term, $orientation);
    }

    // =========================================================================
    // NOTIFICATIONS
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

    // =========================================================================
    // PUBLISH / UNPUBLISH
    // =========================================================================
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
    // PRIVATE: shared notification-sending logic
    // =========================================================================
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
                'class'           => $setting->schoolclass->schoolclass ?? '',
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

    // =========================================================================
    // DELETE / CLONE
    // =========================================================================
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
            $newSetting               = $oldSetting->replicate();
            $newSetting->session_id   = $validated['new_session_id'] ?? $oldSetting->session_id;
            $newSetting->term_id      = $validated['new_term_id'] ?? $oldSetting->term_id;
            $newSetting->is_published = false;
            $newSetting->published_at = null;
            $newSetting->published_by = null;
            $newSetting->editing_by   = null;
            $newSetting->editing_at   = null;
            $newSetting->created_by   = Auth::id();
            $newSetting->updated_by   = Auth::id();
            $newSetting->save();

            $periodMap = [];
            foreach ($oldSetting->periods as $period) {
                $newPeriod             = $period->replicate();
                $newPeriod->setting_id = $newSetting->id;
                $newPeriod->save();
                $periodMap[$period->id] = $newPeriod->id;
            }

            foreach ($oldSetting->constraints as $constraint) {
                $newC = $constraint->replicate(); $newC->setting_id = $newSetting->id; $newC->save();
            }

            $newSlotIds = [];
            foreach ($oldSetting->slots as $slot) {
                if (!isset($periodMap[$slot->period_id])) continue;
                $newSlot = $slot->replicate();
                $newSlot->setting_id = $newSetting->id;
                $newSlot->period_id  = $periodMap[$slot->period_id];
                $newSlot->save();
                $newSlotIds[] = $newSlot->id;
            }

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
    // PRIVATE: After a clone, flag any teacher conflicts introduced.
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
            ->with(['setting.schoolclass', 'subject', 'teacher'])
            ->get()
            ->groupBy(fn($s) => $s->teacher_id . '|' . $s->day . '|' . $s->period_id);

        $conflicts = [];
        foreach ($newSlots as $newSlot) {
            $key = $newSlot->teacher_id . '|' . $newSlot->day . '|' . $newSlot->period_id;
            if (!$others->has($key)) continue;

            $clashing = $others->get($key)->first();
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

    // =========================================================================
    // BULK UPDATE
    // =========================================================================
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

    // =========================================================================
    // SUBSTITUTE REQUESTS
    // =========================================================================
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

    // =========================================================================
    // TEACHER AVAILABILITY
    // =========================================================================
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

    // =========================================================================
    // WORKLOAD DASHBOARD
    // =========================================================================
    public function workloadDashboard(Request $request): JsonResponse
    {
        $sessionId = $request->session_id ?? Schoolsession::where('status', 'Current')->value('id');
        $teachers  = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->with('staffPicture')->get();

        $workloadData = [];
        foreach ($teachers as $teacher) {
            $slots = TimetableSlot::where('teacher_id', $teacher->id)
                ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
                ->with(['setting.schoolclass', 'subject'])->get();

            $dailyLoad = [];
            foreach (self::DAYS as $day) $dailyLoad[$day] = $slots->where('day', $day)->count();

            $workloadData[] = [
                'teacher_id'       => $teacher->id,
                'teacher_name'     => $teacher->name,
                'teacher_picture'  => $teacher->staffPicture ? asset('storage/staff_avatars/' . $teacher->staffPicture->picture) : null,
                'periods_assigned' => $slots->count(),
                'classes_taught'   => $slots->pluck('setting.schoolclass.schoolclass')->filter()->unique()->values(),
                'subjects_taught'  => $slots->pluck('subject.subject')->filter()->unique()->values(),
                'daily_load'       => $dailyLoad,
            ];
        }
        usort($workloadData, fn($a, $b) => $b['periods_assigned'] - $a['periods_assigned']);
        return response()->json(['success' => true, 'workload' => $workloadData]);
    }

    // =========================================================================
    // CLASS SUBJECTS
    // =========================================================================
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

    // =========================================================================
    // TEACHER VIEW
    // =========================================================================
    public function teacherView(Request $request)
    {
        $teacherId = Auth::id();
        $pagetitle = 'My Timetable';

        $sessionId = $request->input('session_id')
            ?? Schoolsession::where('status', 'Current')->value('id')
            ?? Schoolsession::latest('id')->value('id');

        $termId = $request->input('term_id') ?? Schoolterm::latest('id')->value('id');

        $teacher        = User::with('staffPicture')->find($teacherId);
        $teacherPicture = $teacher?->staffPicture
            ? asset('storage/staff_avatars/' . $teacher->staffPicture->picture) : null;

        $slots = TimetableSlot::where('teacher_id', $teacherId)
            ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId)->where('is_active', true))
            ->with(['period', 'subject', 'setting.schoolclass', 'setting.term', 'room'])
            ->get()->groupBy('day');

        $allPeriods = TimetablePeriod::with('setting')->whereIn(
            'setting_id',
            TimetableSetting::where('session_id', $sessionId)->pluck('id')
        )->orderBy('order')->get()->unique('order');

        $periodDayMeta = [];
        $metaCache     = [];
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

        $sessions      = Schoolsession::orderByDesc('id')->get();
        $terms         = Schoolterm::all();
        $days          = self::DAYS;
        $upcomingSlots = $this->getUpcomingSlots($teacherId, $sessionId);
        $weeklySummary = $this->getWeeklySummary($teacherId, $sessionId);

        $icsUrl    = URL::signedRoute('timetable.ics', ['teacherId' => $teacherId], now()->addYears(10));
        $webcalUrl = preg_replace('/^https?:\/\//', 'webcal://', $icsUrl);

        return view('timetable.teacher', compact(
            'pagetitle', 'slots', 'days', 'allPeriods', 'sessions', 'terms',
            'sessionId', 'termId', 'upcomingSlots', 'weeklySummary', 'teacherPicture',
            'periodDayMeta', 'icsUrl', 'webcalUrl'
        ));
    }

    // =========================================================================
    // PRIVATE: Upcoming slots / weekly summary for teacher view
    // =========================================================================
    private function getUpcomingSlots(int $teacherId, int $sessionId): array
    {
        $dayMap     = ['monday' => 0, 'tuesday' => 1, 'wednesday' => 2, 'thursday' => 3, 'friday' => 4];
        $today      = strtolower(now()->format('l'));
        $todayIndex = $dayMap[$today] ?? 0;
        $now        = Carbon::now();

        $slots = TimetableSlot::where('teacher_id', $teacherId)
            ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId)->where('is_active', true))
            ->whereNotNull('subject_id')
            ->with(['period', 'subject', 'setting.schoolclass', 'setting.term', 'room'])->get();

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
                'class'   => $item['slot']->setting?->schoolclass?->schoolclass,
                'room'    => $item['slot']->room?->room_name,
            ])
            ->values()->toArray();
    }

    private function getWeeklySummary(int $teacherId, int $sessionId): array
    {
        $slots = TimetableSlot::where('teacher_id', $teacherId)
            ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId)->where('is_active', true))
            ->whereNotNull('subject_id')
            ->with(['period', 'subject', 'setting.schoolclass', 'room'])->get();

        $summary = [];
        foreach (self::DAYS as $day) {
            $ds = $slots->where('day', $day);
            $summary[$day] = [
                'count'    => $ds->count(),
                'subjects' => $ds->pluck('subject.subject')->filter()->unique()->values()->toArray(),
                'classes'  => $ds->pluck('setting.schoolclass.schoolclass')->filter()->unique()->values()->toArray(),
            ];
        }
        return $summary;
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
    // PRIVATE: Build the whole-school timetable PDF — one page per class.
    // =========================================================================
    private function exportWholeSchoolPdf(array $allTimetables, ?SchoolInformation $schoolInfo, ?Schoolsession $session, ?Schoolterm $term, string $orientation)
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
        ])->setPaper($orientation === 'vertical' ? 'a4' : 'a3', 'landscape');

        $filename = 'whole-school-timetable-' . str_replace([' ', '/'], '-', $sessionName) . '.pdf';

        return $pdf->stream($filename);
    }
}