<?php

// app/Http/Controllers/TimetableController.php

namespace App\Http\Controllers;

use App\Mail\TimetableNotificationMail;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\Schoolclass;
use App\Models\SchoolInformation;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Subject;
use App\Models\Subjectclass;
use App\Models\SubjectTeacher;
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

    public function __construct()
    {
        $this->middleware('permission:View timetable|Create timetable|Edit timetable|Delete timetable|Generate timetable', ['only' => ['index', 'getSetting', 'getGrid']]);
        $this->middleware('permission:Create timetable', ['only' => ['setup', 'saveSettings']]);
        $this->middleware('permission:Edit timetable', ['only' => ['saveSlot', 'bulkUpdateSlots', 'cloneSetting']]);
        $this->middleware('permission:Delete timetable', ['only' => ['deleteSetting']]);
        $this->middleware('permission:Generate timetable', ['only' => ['autoGenerate']]);
        $this->middleware('permission:View my timetable', ['only' => ['teacherView']]);
        $this->middleware('permission:Manage timetable settings', ['only' => ['saveSettings']]);
        $this->middleware('permission:Manage timetable constraints', ['only' => ['saveConstraints']]);
        $this->middleware('permission:View timetable reports', ['only' => ['workloadDashboard', 'generateAnalytics']]);
        $this->middleware('permission:Export timetable', ['only' => ['export', 'exportWholeSchool']]);
        $this->middleware('permission:Request substitute', ['only' => ['requestSubstitute']]);
        $this->middleware('permission:Approve substitute', ['only' => ['approveSubstitute']]);
        $this->middleware('permission:View substitute requests', ['only' => ['getSubstituteRequests']]);
        $this->middleware('permission:Manage teacher availability', ['only' => ['saveTeacherAvailability', 'getTeacherAvailability']]);
        $this->middleware('permission:Check timetable conflicts', ['only' => ['checkConflicts']]);
        $this->middleware('permission:Send timetable notifications', ['only' => ['sendNotifications']]);
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

        // All slots this teacher occupies across ALL classes in same session/term
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
                // Skip if teacher already busy at this time (any class)
                if ($teacherBusyKeys->has($day . '_' . $period->id)) continue;
                // Check if slot in THIS class is free
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
    // GET SETTING
    // =========================================================================
    public function getSetting(int $settingId): JsonResponse
    {
        $setting = TimetableSetting::with(['periods', 'constraints.subject', 'session', 'term'])->findOrFail($settingId);

        $schoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
            ->where('schoolclass.id', $setting->schoolclass_id)->first();
        $setting->setRelation('schoolclass', $schoolclass);

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

        return response()->json(['success' => true, 'setting' => $setting, 'available_subjects' => $availableSubjects]);
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
            'success'    => true,
            'setting'    => $setting,
            'periods'    => $setting->periods,
            'grid'       => $grid,
            'days'       => $setting->active_days ?? self::DAYS,
            'teachers'   => $allTeachers,
            'rooms'      => $rooms,
            'class_name' => $this->getClassName($setting->schoolclass),
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
            $isNew   = $setting->periods()->count() === 0;

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
    // SAVE CONSTRAINTS
    // =========================================================================
    public function saveConstraints(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id'                      => 'required|exists:timetable_settings,id',
            'constraints'                     => 'required|array',
            'constraints.*.subject_id'        => 'required|exists:subject,id',
            'constraints.*.periods_per_week'  => 'required|integer|min:1|max:10',
            'constraints.*.allow_double'      => 'boolean',
            'constraints.*.max_double'        => 'integer|min:0|max:5',
            'constraints.*.preferred_days'    => 'nullable|array',
            'constraints.*.avoid_days'        => 'nullable|array',
            'constraints.*.preferred_periods' => 'nullable|array',
            'constraints.*.is_compulsory'     => 'boolean',
        ]);

        DB::transaction(function () use ($validated) {
            TimetableConstraint::where('setting_id', $validated['setting_id'])->delete();
            foreach ($validated['constraints'] as $c) {
                TimetableConstraint::create([
                    'setting_id'                  => $validated['setting_id'],
                    'subject_id'                  => $c['subject_id'],
                    'periods_per_week'             => $c['periods_per_week'],
                    'allow_double_period'          => $c['allow_double'] ?? false,
                    'max_double_periods_per_week'  => $c['max_double'] ?? 1,
                    'preferred_days'               => $c['preferred_days'] ?? null,
                    'avoid_days'                   => $c['avoid_days'] ?? null,
                    'preferred_periods'            => $c['preferred_periods'] ?? null,
                    'is_compulsory'                => $c['is_compulsory'] ?? true,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // REAL-TIME SLOT CONFLICT CHECK
    // Called from the modal as the user selects teacher / room — no save yet.
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

        // ── Teacher conflict ──────────────────────────────────────────────────
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

            // Workload warning — 4+ periods on the same day across all classes
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

        // ── Room conflict (timetable slots) ───────────────────────────────────
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

            // Room booking conflict
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
    // SAVE SLOT — real-time cross-class teacher + room conflict detection
    // =========================================================================
    public function saveSlot(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id'  => 'required|exists:timetable_settings,id',
            'period_id'   => 'required|exists:timetable_periods,id',
            'day'         => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'subject_id'  => 'nullable|exists:subject,id',
            'teacher_id'  => 'nullable|exists:users,id',
            'room_id'     => 'nullable|exists:rooms,id',
            'is_double'   => 'boolean',
            'is_free'     => 'boolean',
            'notes'       => 'nullable|string|max:191',
            'force_save'  => 'boolean',
        ]);

        $currentSetting = TimetableSetting::findOrFail($validated['setting_id']);
        $sessionId      = $currentSetting->session_id;
        $termId         = $currentSetting->term_id;

        // ── Resolve current class name for messages ───────────────────────────
        $currentSchoolclass = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm as arm_name'])
            ->where('schoolclass.id', $currentSetting->schoolclass_id)->first();
        $currentSetting->setRelation('schoolclass', $currentSchoolclass);
        $currentClassName = $this->getClassName($currentSetting->schoolclass);

        if (empty($validated['force_save'])) {

            // ── TEACHER CONFLICT ──────────────────────────────────────────────
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

            // ── ROOM CONFLICT (timetable slots) ───────────────────────────────
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

                // ── ROOM BOOKING CONFLICT ─────────────────────────────────────
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

        // ── No conflict — save ────────────────────────────────────────────────
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

        return response()->json(['success' => true, 'slot' => $slot->load(['subject', 'teacher', 'room'])]);
    }

    // =========================================================================
    // CHECK CONFLICTS — full cross-class teacher + room detection
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

        // All active, non-free slots in this session/term
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

        // Hydrate schoolclass with arm_name for each slot's setting
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

        // ── Teacher double-booking ────────────────────────────────────────────
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

        // ── Room double-booking ───────────────────────────────────────────────
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

        // Re-hydrate schoolclass for room slots
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
    // AUTO-GENERATE — respects cross-class teacher occupancy
    // =========================================================================
    public function autoGenerate(Request $request): JsonResponse
    {
        $validated = $request->validate(['setting_id' => 'required|exists:timetable_settings,id']);

        try {
            DB::beginTransaction();

            $setting       = TimetableSetting::with(['periods', 'constraints.subject'])->findOrFail($validated['setting_id']);
            $lessonPeriods = $setting->periods->where('type', 'lesson')->values();
            $days          = $setting->active_days ?? self::DAYS;
            $constraints   = $setting->constraints->keyBy('subject_id');
            $classId       = $setting->schoolclass_id;
            $sessionId     = $setting->session_id;
            $termId        = $setting->term_id;

            $subjectTeachers = SubjectTeacher::where('sessionid', $sessionId)
                ->whereHas('subjectclass', fn($q) => $q->where('schoolclassid', $classId))
                ->with(['subject', 'staff'])->get()->groupBy('subjectid');

            // Pre-load cross-class teacher occupancy for this session/term
            $crossOccupied = [];
            $crossClassSlots = TimetableSlot::whereHas('setting', function($q) use ($sessionId, $termId, $setting) {
                    $q->where('session_id', $sessionId)->where('is_active', true)->where('id', '!=', $setting->id);
                    if ($termId) $q->where('term_id', $termId);
                    else         $q->whereNull('term_id');
                })
                ->whereNotNull('teacher_id')->where('is_free', false)
                ->get(['teacher_id', 'period_id', 'day']);

            foreach ($crossClassSlots as $occ) {
                $crossOccupied[$occ->teacher_id][$occ->day][] = $occ->period_id;
            }

            TimetableSlot::where('setting_id', $setting->id)->delete();

            $slotPool       = $this->buildWeightedSlotPool($days, $lessonPeriods);
            $teacherDaySlot = [];
            $placed         = [];

            $requirements = $constraints->sortByDesc(fn($c) => ($c->is_compulsory ? 100 : 0) + $c->periods_per_week)->values();

            foreach ($requirements as $constraint) {
                $subjectId   = $constraint->subject_id;
                $needed      = $constraint->periods_per_week;
                $allowDouble = $constraint->allow_double_period;
                $maxDouble   = $constraint->max_double_periods_per_week;
                $preferDays  = $constraint->preferred_days ?? [];
                $avoidDays   = $constraint->avoid_days ?? [];
                $doubleCount = 0;

                $teacherEntry = $subjectTeachers->get($subjectId)?->first();
                $teacherId    = $teacherEntry?->staffid;

                $scoredSlots = [];
                foreach ($slotPool as $slot) {
                    $day      = $slot['day'];
                    $periodId = $slot['period_id'];
                    $key      = $day . '_' . $periodId;
                    if (isset($placed[$key])) continue;
                    // Skip if teacher already assigned in this class
                    if ($teacherId && in_array($periodId, $teacherDaySlot[$teacherId][$day] ?? [])) continue;
                    // Skip if teacher already assigned in another class
                    if ($teacherId && in_array($periodId, $crossOccupied[$teacherId][$day] ?? [])) continue;

                    $score = 0;
                    if (in_array($day, $preferDays)) $score += 10;
                    if (in_array($day, $avoidDays))  $score -= 10;
                    $scoredSlots[] = ['slot' => $slot, 'score' => $score, 'key' => $key];
                }

                usort($scoredSlots, fn($a, $b) => $b['score'] - $a['score']);

                $placedThisSubject = 0;
                foreach ($scoredSlots as $scored) {
                    if ($placedThisSubject >= $needed) break;
                    $slot     = $scored['slot'];
                    $day      = $slot['day'];
                    $periodId = $slot['period_id'];
                    $key      = $scored['key'];
                    if (isset($placed[$key])) continue;

                    TimetableSlot::create([
                        'setting_id' => $setting->id,
                        'period_id'  => $periodId,
                        'day'        => $day,
                        'subject_id' => $subjectId,
                        'teacher_id' => $teacherId,
                        'is_double'  => false,
                        'is_free'    => false,
                    ]);
                    $placed[$key] = $subjectId;
                    if ($teacherId) {
                        $teacherDaySlot[$teacherId][$day][] = $periodId;
                        // Also mark in crossOccupied so subsequent subjects respect it
                        $crossOccupied[$teacherId][$day][] = $periodId;
                    }
                    $placedThisSubject++;

                    // Double period
                    if ($allowDouble && $doubleCount < $maxDouble && $placedThisSubject < $needed) {
                        $nextPeriod = $this->getNextLessonPeriod($lessonPeriods, $periodId);
                        if ($nextPeriod) {
                            $nextKey         = $day . '_' . $nextPeriod->id;
                            $teacherConflict = $teacherId && (
                                in_array($nextPeriod->id, $teacherDaySlot[$teacherId][$day] ?? []) ||
                                in_array($nextPeriod->id, $crossOccupied[$teacherId][$day] ?? [])
                            );
                            if (!isset($placed[$nextKey]) && !$teacherConflict) {
                                TimetableSlot::create([
                                    'setting_id' => $setting->id,
                                    'period_id'  => $nextPeriod->id,
                                    'day'        => $day,
                                    'subject_id' => $subjectId,
                                    'teacher_id' => $teacherId,
                                    'is_double'  => true,
                                    'is_free'    => false,
                                ]);
                                $placed[$nextKey] = $subjectId;
                                if ($teacherId) {
                                    $teacherDaySlot[$teacherId][$day][] = $nextPeriod->id;
                                    $crossOccupied[$teacherId][$day][]  = $nextPeriod->id;
                                }
                                $placedThisSubject++;
                                $doubleCount++;
                            }
                        }
                    }
                }
            }

            // Fill remaining slots as free
            foreach ($slotPool as $slot) {
                $key = $slot['day'] . '_' . $slot['period_id'];
                if (!isset($placed[$key])) {
                    TimetableSlot::create([
                        'setting_id' => $setting->id,
                        'period_id'  => $slot['period_id'],
                        'day'        => $slot['day'],
                        'subject_id' => null,
                        'teacher_id' => null,
                        'is_free'    => true,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Timetable generated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('autoGenerate failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function buildWeightedSlotPool($days, $lessonPeriods): array
    {
        $pool = [];
        foreach ($days as $day) {
            foreach ($lessonPeriods as $period) {
                $pool[] = ['day' => $day, 'period_id' => $period->id, 'period_order' => $period->order];
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
            'pdf'   => $this->exportPdf($setting, $periods, $days, $grid, $subjectColors, $className, $sessionName, $termName, $orientation),
            default => $this->exportCsv($setting, $periods, $days, $grid, $className, $sessionName),
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
                ];
            }
            $allTimetables[] = [
                'class_name' => $className,
                'periods'    => $setting->periods,
                'grid'       => $grid,
                'days'       => $setting->active_days ?? self::DAYS,
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

        $byTeacher = $setting->slots->whereNotNull('teacher_id')->groupBy('teacher_id');
        $sent      = 0;

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
                'type'      => $validated['type'],
                'generated' => now()->format('d M Y H:i'),
            ];

            try {
                Mail::to($teacher->email)->send(new TimetableNotificationMail($notifData));
                foreach ($teacherSlots as $slot) {
                    TimetableNotification::create([
                        'teacher_id'   => $teacherId, 'slot_id'    => $slot->id,
                        'type'         => $validated['type'], 'email' => $teacher->email,
                        'scheduled_at' => now(), 'sent_at'   => now(),
                        'status'       => 'sent', 'payload'   => json_encode($notifData),
                    ]);
                }
                $sent++;
            } catch (\Exception $e) {
                Log::error('Timetable notification failed', ['teacher_id' => $teacherId, 'error' => $e->getMessage()]);
            }
        }

        return response()->json(['success' => true, 'message' => "Notifications sent to {$sent} teacher(s).", 'sent' => $sent]);
    }

    // =========================================================================
    // DELETE / CLONE
    // =========================================================================
    public function deleteSetting(int $settingId): JsonResponse
    {
        $setting = TimetableSetting::findOrFail($settingId);
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
        ]);

        $oldSetting = TimetableSetting::with(['periods', 'constraints', 'slots'])->findOrFail($validated['setting_id']);

        DB::beginTransaction();
        try {
            $newSetting             = $oldSetting->replicate();
            $newSetting->session_id = $validated['new_session_id'] ?? $oldSetting->session_id;
            $newSetting->term_id    = $validated['new_term_id'] ?? $oldSetting->term_id;
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
            foreach ($oldSetting->slots as $slot) {
                if (!isset($periodMap[$slot->period_id])) continue;
                $newSlot = $slot->replicate();
                $newSlot->setting_id = $newSetting->id;
                $newSlot->period_id  = $periodMap[$slot->period_id];
                $newSlot->save();
            }

            DB::commit();
            return response()->json(['success' => true, 'setting_id' => $newSetting->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
        $slot = TimetableSlot::findOrFail($validated['slot_id']);
        if ($slot->teacher_id != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You can only request substitutes for your own classes'], 403);
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

        $allPeriods = TimetablePeriod::whereIn(
            'setting_id',
            TimetableSetting::where('session_id', $sessionId)->pluck('id')
        )->orderBy('order')->get()->unique('order');

        $sessions      = Schoolsession::orderByDesc('id')->get();
        $terms         = Schoolterm::all();
        $days          = self::DAYS;
        $upcomingSlots = $this->getUpcomingSlots($teacherId, $sessionId);
        $weeklySummary = $this->getWeeklySummary($teacherId, $sessionId);

        return view('timetable.teacher', compact(
            'pagetitle', 'slots', 'days', 'allPeriods', 'sessions', 'terms',
            'sessionId', 'termId', 'upcomingSlots', 'weeklySummary', 'teacherPicture'
        ));
    }

    // =========================================================================
    // PRIVATE: PDF EXPORT HELPERS
    // =========================================================================
    private function exportCsv($setting, $periods, $days, $grid, $className, $sessionName)
    {
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', "timetable_{$className}_{$sessionName}.csv");
        $handle   = fopen('php://temp', 'w+');
        fwrite($handle, "\xEF\xBB\xBF");

        $header = ['Period', 'Time'];
        foreach ($days as $day) { $header[] = "{$day} (Subject)"; $header[] = "{$day} (Teacher)"; }
        fputcsv($handle, $header);

        foreach ($periods as $period) {
            $row = [
                $period->name,
                $this->formatTime($period->start_time) . ' – ' . $this->formatTime($period->end_time),
            ];
            foreach ($days as $day) {
                $s     = $grid[$period->id][$day] ?? ['subject' => '—', 'teacher' => ''];
                $row[] = $s['subject'];
                $row[] = $s['teacher'] ?: '—';
            }
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    private function exportPdf($setting, $periods, $days, $grid, $subjectColors, $className, $sessionName, $termName, $orientation = 'horizontal')
    {
        $schoolInfo    = SchoolInformation::getActiveSchool();
        $schoolName    = $schoolInfo?->school_name ?? config('app.name', 'School');
        $schoolLogo    = $schoolInfo?->getLogoWithFallbackAttribute();
        $schoolAddress = $schoolInfo?->school_address ?? '';
        $schoolPhone   = $schoolInfo?->school_phone ?? '';
        $schoolEmail   = $schoolInfo?->school_email ?? '';
        $schoolMotto   = $schoolInfo?->school_motto ?? '';
        $generatedAt   = now()->format('d F Y, H:i');
        $generatedBy   = Auth::user()->name;

        $subjectColorCss = '';
        foreach ($subjectColors as $subjectId => $cIdx) {
            $bg = self::SUBJECT_PALETTE[$cIdx];
            $subjectColorCss .= ".subj-{$subjectId}{background:{$bg}!important}\n";
        }

        return $orientation === 'vertical'
            ? $this->exportPdfVertical($periods, $days, $grid, $subjectColorCss, $className, $sessionName, $termName, $schoolName, $schoolLogo, $schoolAddress, $schoolPhone, $schoolEmail, $schoolMotto, $generatedAt, $generatedBy)
            : $this->exportPdfHorizontal($periods, $days, $grid, $subjectColorCss, $className, $sessionName, $termName, $schoolName, $schoolLogo, $schoolAddress, $schoolPhone, $schoolEmail, $schoolMotto, $generatedAt, $generatedBy);
    }

    private function buildSchoolHeaderHtml(string $schoolName, ?string $schoolLogo, string $schoolAddress, string $schoolPhone, string $schoolEmail, string $schoolMotto): string
    {
        $logoHtml = $schoolLogo
            ? '<img src="' . $schoolLogo . '" class="school-logo" alt="School Logo">'
            : '<div class="school-logo-placeholder">🏫</div>';

        return <<<HTML
<div class="school-header">
    <div class="school-logo-wrap">{$logoHtml}</div>
    <div class="school-info">
        <div class="school-name">{$schoolName}</div>
        <div class="school-motto">{$schoolMotto}</div>
        <div class="school-contact">
            <div class="contact-address">{$schoolAddress}</div>
            <div class="contact-details">
                <span class="contact-item"><span class="contact-icon">📞</span> {$schoolPhone}</span>
                <span class="contact-item"><span class="contact-icon">✉️</span> {$schoolEmail}</span>
            </div>
        </div>
    </div>
</div>
HTML;
    }

    private function getSharedPdfCss(): string
    {
        return <<<CSS
@page { size: A4 landscape; margin: 12mm 10mm; }
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', 'Roboto', Arial, sans-serif; font-size: 10px; color: #1a1a2e; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.school-header { text-align: center; padding-bottom: 16px; margin-bottom: 16px; border-bottom: 2px solid #e0e7ff; position: relative; }
.school-header::after { content: ''; position: absolute; bottom: -2px; left: 25%; width: 50%; height: 2px; background: linear-gradient(90deg, #1565C0, #6A1B9A, #1565C0); }
.school-logo-wrap { margin-bottom: 12px; }
.school-logo { height: 80px; width: auto; max-width: 200px; object-fit: contain; display: inline-block; }
.school-logo-placeholder { width: 80px; height: 80px; background: linear-gradient(135deg, #1565C0, #6A1B9A); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 42px; color: white; }
.school-name { font-size: 22px; font-weight: 800; color: #1565C0; letter-spacing: 0.5px; margin-bottom: 5px; text-transform: uppercase; }
.school-motto { font-size: 11px; color: #6A1B9A; font-style: italic; margin-bottom: 8px; }
.school-contact { font-size: 8.5px; color: #555; }
.contact-address { margin-bottom: 4px; }
.contact-details { display: flex; justify-content: center; gap: 20px; }
.contact-item { display: inline-flex; align-items: center; gap: 5px; }
.doc-title { font-size: 18px; font-weight: 800; color: #1a1a2e; text-align: center; margin: 12px 0 5px; text-transform: uppercase; letter-spacing: 1.5px; }
.doc-title::before, .doc-title::after { content: '✦'; font-size: 12px; color: #1565C0; margin: 0 12px; opacity: 0.6; }
.doc-meta { font-size: 10px; color: #666; text-align: center; margin-bottom: 18px; padding-bottom: 8px; border-bottom: 1px dashed #ddd; }
.class-section { margin-bottom: 35px; page-break-inside: avoid; }
.class-title { font-size: 14px; font-weight: 800; color: #1565C0; margin: 20px 0 12px; padding: 8px 15px; background: linear-gradient(135deg, #f0f4ff, #e8edf5); border-left: 5px solid #1565C0; border-radius: 0 8px 8px 0; display: inline-block; }
table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
th, td { border: 1px solid #e2e8f0; vertical-align: middle; padding: 8px 6px; }
.period-header { background: linear-gradient(135deg, #1a1a2e, #16213e); color: #fff; font-size: 9px; font-weight: 700; text-align: center; width: 80px; }
.period-header .pname { font-weight: 800; font-size: 9px; text-transform: uppercase; }
.period-header .ptime-small { font-size: 7px; opacity: 0.8; margin-top: 2px; }
.day-header { color: #fff; font-size: 11px; font-weight: 700; text-align: center; padding: 10px 6px; text-transform: uppercase; }
.day-header.monday    { background: linear-gradient(135deg, #1565C0, #0d47a1); }
.day-header.tuesday   { background: linear-gradient(135deg, #6A1B9A, #4a0072); }
.day-header.wednesday { background: linear-gradient(135deg, #1B5E20, #0a3d12); }
.day-header.thursday  { background: linear-gradient(135deg, #E65100, #bf360c); }
.day-header.friday    { background: linear-gradient(135deg, #880E4F, #4a0024); }
.period-cell { background: linear-gradient(135deg, #f8fafc, #f1f5f9); text-align: center; font-weight: 600; }
.pname { font-weight: 800; font-size: 10px; color: #1e293b; }
.ptime { font-size: 8px; color: #64748b; margin-top: 2px; font-family: monospace; }
.day-cell { font-weight: 800; text-align: center; width: 70px; font-size: 11px; text-transform: uppercase; }
.slot-cell { text-align: center; padding: 8px 4px; background: #fff; }
.sname { font-weight: 800; font-size: 10px; color: #1e293b; margin-bottom: 3px; }
.scode { font-size: 7px; color: #6c757d; font-style: italic; margin-bottom: 2px; }
.tname { font-size: 8px; color: #4b5563; margin-bottom: 2px; }
.room { font-size: 7px; color: #059669; margin-top: 2px; display: inline-flex; align-items: center; gap: 2px; background: #ecfdf5; padding: 1px 4px; border-radius: 4px; }
.break-cell { background: linear-gradient(135deg, #fffbeb, #fef3c7) !important; text-align: center; }
.break-lbl { font-size: 8px; color: #d97706; font-weight: 700; }
.free-cell { background: #f9fafb !important; text-align: center; }
.free-lbl { font-size: 8px; color: #9ca3af; font-style: italic; }
.footer { margin-top: 15px; padding-top: 10px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 7px; color: #94a3b8; }
.print-btn { position: fixed; top: 20px; right: 20px; background: linear-gradient(135deg, #1565C0, #0d47a1); color: white; border: none; padding: 10px 24px; border-radius: 40px; cursor: pointer; font-size: 13px; font-weight: 700; z-index: 999; }
@media print { .print-btn { display: none !important; } .class-section { page-break-inside: avoid; } }
CSS;
    }

    private function buildSlotCellHtml(array $slot): string
    {
        $subjectName = $slot['subject'] ?? '—';
        $teacherName = $slot['teacher'] ?? '';
        $roomName    = $slot['room'] ?? '';

        $teacherHtml = (!empty($teacherName) && $teacherName !== '—')
            ? '<div class="tname">👨‍🏫 ' . htmlspecialchars($teacherName) . '</div>' : '';
        $roomHtml    = !empty($roomName)
            ? '<div class="room">📍 ' . htmlspecialchars($roomName) . '</div>' : '';
        $codeHtml    = !empty($slot['subject_code'])
            ? '<div class="scode">' . htmlspecialchars($slot['subject_code']) . '</div>' : '';

        return '<td class="slot-cell">'
            . '<div class="sname">' . htmlspecialchars($subjectName) . '</div>'
            . $codeHtml . $teacherHtml . $roomHtml . '</td>';
    }

    private function exportPdfHorizontal($periods, $days, $grid, $subjectColorCss, $className, $sessionName, $termName, $schoolName, $schoolLogo, $schoolAddress, $schoolPhone, $schoolEmail, $schoolMotto, $generatedAt, $generatedBy)
    {
        $colPct = round(85 / max(count($days), 1), 2);
        $dayHeaders = '';
        foreach ($days as $day) {
            $dayHeaders .= '<th class="day-header ' . strtolower($day) . '" style="width:' . $colPct . '%">' . htmlspecialchars($day) . '</th>';
        }

        $tableRows = '';
        foreach ($periods as $period) {
            $isBreak = $period->is_break ?? false;
            $tableRows .= '<tr' . ($isBreak ? ' class="break-row"' : '') . '>';
            $tableRows .= '<td class="period-cell"><div class="pname">' . htmlspecialchars($period->name) . '</div>'
                . '<div class="ptime">' . htmlspecialchars($this->formatTime($period->start_time) . ' – ' . $this->formatTime($period->end_time)) . '</div></td>';
            foreach ($days as $day) {
                $slot   = $grid[$period->id][$day] ?? null;
                $isFree = !$slot || ($slot['is_free'] ?? false) || empty($slot['subject']) || $slot['subject'] === '—';
                if ($isBreak)      $tableRows .= '<td class="break-cell"><span class="break-lbl">☕ BREAK</span></td>';
                elseif ($isFree)   $tableRows .= '<td class="free-cell"><span class="free-lbl">— FREE —</span></td>';
                else               $tableRows .= $this->buildSlotCellHtml($slot);
            }
            $tableRows .= '</tr>';
        }

        $sharedCss  = $this->getSharedPdfCss();
        $headerHtml = $this->buildSchoolHeaderHtml($schoolName, $schoolLogo, $schoolAddress, $schoolPhone, $schoolEmail, $schoolMotto);

        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Timetable — ' . htmlspecialchars($className) . '</title>'
            . '<style>' . $sharedCss . "\n" . $subjectColorCss . '</style></head><body>'
            . '<button class="print-btn" onclick="window.print()">🖨️ Print / Save PDF</button>'
            . $headerHtml
            . '<div class="doc-title">CLASS TIMETABLE</div>'
            . '<div class="doc-meta">📖 ' . htmlspecialchars($className) . ' • 📅 ' . htmlspecialchars($sessionName) . ' • ' . htmlspecialchars($termName) . '</div>'
            . '<table><thead><tr><th class="period-header" style="width:90px">PERIOD</th>' . $dayHeaders . '</tr></thead><tbody>' . $tableRows . '</tbody></table>'
            . '<div class="footer"><div class="footer-left">Generated: ' . htmlspecialchars($generatedAt) . '</div>'
            . '<div style="text-align:center">Generated by: ' . htmlspecialchars($generatedBy) . '</div>'
            . '<div style="text-align:right">☕ Break | ▯ Free period</div></div></body></html>';

        return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function exportPdfVertical($periods, $days, $grid, $subjectColorCss, $className, $sessionName, $termName, $schoolName, $schoolLogo, $schoolAddress, $schoolPhone, $schoolEmail, $schoolMotto, $generatedAt, $generatedBy)
    {
        $colPct        = round(85 / max(count($periods), 1), 2);
        $periodHeaders = '';
        foreach ($periods as $period) {
            $periodHeaders .= '<th class="period-header" style="width:' . $colPct . '%">'
                . '<div class="pname">' . htmlspecialchars($period->name) . '</div>'
                . '<div class="ptime-small">' . htmlspecialchars($this->formatTime($period->start_time) . '–' . $this->formatTime($period->end_time)) . '</div></th>';
        }

        $tableRows = '';
        foreach ($days as $day) {
            $hc = self::DAY_COLORS[$day] ?? '#333';
            $tableRows .= '<tr><td class="day-cell" style="background:linear-gradient(135deg,' . $hc . ',' . $hc . 'cc);color:#fff">' . htmlspecialchars($day) . '</td>';
            foreach ($periods as $period) {
                $slot   = $grid[$period->id][$day] ?? null;
                $isFree = !$slot || ($slot['is_free'] ?? false) || empty($slot['subject']) || $slot['subject'] === '—';
                if ($period->is_break ?? false) $tableRows .= '<td class="break-cell"><span class="break-lbl">☕ BREAK</span></td>';
                elseif ($isFree)                $tableRows .= '<td class="free-cell"><span class="free-lbl">— FREE —</span></td>';
                else                            $tableRows .= $this->buildSlotCellHtml($slot);
            }
            $tableRows .= '</tr>';
        }

        $sharedCss  = $this->getSharedPdfCss();
        $headerHtml = $this->buildSchoolHeaderHtml($schoolName, $schoolLogo, $schoolAddress, $schoolPhone, $schoolEmail, $schoolMotto);

        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Timetable — ' . htmlspecialchars($className) . '</title>'
            . '<style>' . $sharedCss . "\n" . $subjectColorCss . '</style></head><body>'
            . '<button class="print-btn" onclick="window.print()">🖨️ Print / Save PDF</button>'
            . $headerHtml
            . '<div class="doc-title">CLASS TIMETABLE</div>'
            . '<div class="doc-meta">📖 ' . htmlspecialchars($className) . ' • 📅 ' . htmlspecialchars($sessionName) . ' • ' . htmlspecialchars($termName) . '</div>'
            . '<table><thead><tr><th class="period-header" style="width:70px">DAY / PERIOD</th>' . $periodHeaders . '</tr></thead><tbody>' . $tableRows . '</tbody></table>'
            . '<div class="footer"><div class="footer-left">Generated: ' . htmlspecialchars($generatedAt) . '</div>'
            . '<div style="text-align:center">Generated by: ' . htmlspecialchars($generatedBy) . '</div>'
            . '<div style="text-align:right">☕ Break | ▯ Free period</div></div></body></html>';

        return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function exportWholeSchoolPdf($allTimetables, $schoolInfo, $session, $term, $orientation = 'horizontal')
    {
        $schoolName    = $schoolInfo?->school_name ?? config('app.name', 'School');
        $schoolLogo    = $schoolInfo?->getLogoWithFallbackAttribute();
        $schoolAddress = $schoolInfo?->school_address ?? '';
        $schoolPhone   = $schoolInfo?->school_phone ?? '';
        $schoolEmail   = $schoolInfo?->school_email ?? '';
        $schoolMotto   = $schoolInfo?->school_motto ?? '';
        $generatedAt   = now()->format('d F Y, H:i');
        $generatedBy   = Auth::user()->name;
        $sessionName   = $session->session ?? 'Current Session';
        $termName      = $term?->term ?? 'All Terms';

        $headerHtml     = $this->buildSchoolHeaderHtml($schoolName, $schoolLogo, $schoolAddress, $schoolPhone, $schoolEmail, $schoolMotto);
        $sharedCss      = $this->getSharedPdfCss();
        $timetablesHtml = '';

        foreach ($allTimetables as $tt) {
            $eClassName = htmlspecialchars($tt['class_name']);

            if ($orientation === 'vertical') {
                $colPct        = round(85 / max(count($tt['periods']), 1), 2);
                $periodHeaders = '';
                foreach ($tt['periods'] as $period) {
                    $periodHeaders .= '<th class="period-header" style="width:' . $colPct . '%">'
                        . '<div class="pname">' . htmlspecialchars($period->name) . '</div>'
                        . '<div class="ptime-small">' . htmlspecialchars($this->formatTime($period->start_time) . '–' . $this->formatTime($period->end_time)) . '</div></th>';
                }
                $tableRows = '';
                foreach ($tt['days'] as $day) {
                    $hc = self::DAY_COLORS[$day] ?? '#333';
                    $tableRows .= '<tr><td class="day-cell" style="background:linear-gradient(135deg,' . $hc . ',' . $hc . 'cc);color:#fff">' . htmlspecialchars($day) . '</td>';
                    foreach ($tt['periods'] as $period) {
                        $slot   = $tt['grid'][$period->id][$day] ?? null;
                        $isFree = !$slot || empty($slot['subject']) || $slot['subject'] === '—' || $slot['subject'] === 'FREE';
                        if ($period->is_break ?? false) $tableRows .= '<td class="break-cell"><span class="break-lbl">☕ BREAK</span></td>';
                        elseif ($isFree)                $tableRows .= '<td class="free-cell"><span class="free-lbl">— FREE —</span></td>';
                        else                            $tableRows .= $this->buildSlotCellHtml($slot);
                    }
                    $tableRows .= '</tr>';
                }
                $timetablesHtml .= '<div class="class-section"><div class="class-title">📖 ' . $eClassName . '</div>'
                    . '<table><thead><tr><th class="period-header" style="width:70px">DAY / PERIOD</th>' . $periodHeaders . '</tr></thead><tbody>' . $tableRows . '</tbody></table></div>';
            } else {
                $colPct     = round(85 / max(count($tt['days']), 1), 2);
                $dayHeaders = '';
                foreach ($tt['days'] as $day) {
                    $dayHeaders .= '<th class="day-header ' . strtolower($day) . '" style="width:' . $colPct . '%">' . htmlspecialchars($day) . '</th>';
                }
                $tableRows = '';
                foreach ($tt['periods'] as $period) {
                    $isBreak = $period->is_break ?? false;
                    $tableRows .= '<tr' . ($isBreak ? ' class="break-row"' : '') . '>';
                    $tableRows .= '<td class="period-cell"><div class="pname">' . htmlspecialchars($period->name) . '</div>'
                        . '<div class="ptime">' . htmlspecialchars($this->formatTime($period->start_time) . ' – ' . $this->formatTime($period->end_time)) . '</div></td>';
                    foreach ($tt['days'] as $day) {
                        $slot   = $tt['grid'][$period->id][$day] ?? null;
                        $isFree = !$slot || empty($slot['subject']) || $slot['subject'] === '—' || $slot['subject'] === 'FREE';
                        if ($isBreak)    $tableRows .= '<td class="break-cell"><span class="break-lbl">☕ BREAK</span></td>';
                        elseif ($isFree) $tableRows .= '<td class="free-cell"><span class="free-lbl">— FREE —</span></td>';
                        else             $tableRows .= $this->buildSlotCellHtml($slot);
                    }
                    $tableRows .= '</tr>';
                }
                $timetablesHtml .= '<div class="class-section"><div class="class-title">📖 ' . $eClassName . '</div>'
                    . '<table><thead><tr><th class="period-header" style="width:85px">PERIOD</th>' . $dayHeaders . '</tr></thead><tbody>' . $tableRows . '</tbody></table></div>';
            }
        }

        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
            . '<title>Whole School Timetable - ' . htmlspecialchars($sessionName) . '</title>'
            . '<style>' . $sharedCss . '</style></head><body>'
            . '<button class="print-btn" onclick="window.print()">🖨️ Print / Save PDF</button>'
            . $headerHtml
            . '<div class="doc-title">WHOLE SCHOOL TIMETABLE</div>'
            . '<div class="doc-meta">📅 ' . htmlspecialchars($sessionName) . ' • ' . htmlspecialchars($termName) . '</div>'
            . $timetablesHtml
            . '<div class="footer"><div class="footer-left">Generated: ' . htmlspecialchars($generatedAt) . '</div>'
            . '<div style="text-align:center">Generated by: ' . htmlspecialchars($generatedBy) . '</div>'
            . '<div style="text-align:right">☕ Break | ▯ Free period</div></div></body></html>';

        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'inline; filename="whole_school_timetable_' . htmlspecialchars($sessionName) . '.pdf"');
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
            ->with(['period', 'subject', 'setting.schoolclass', 'room'])->get();

        return $slots->filter(function ($slot) use ($now, $today) {
                if (strtolower($slot->day) === $today) {
                    try { return Carbon::createFromFormat('H:i:s', $slot->period->start_time)->greaterThan($now); }
                    catch (\Exception $e) { return true; }
                }
                return true;
            })
            ->sortBy(function ($s) use ($dayMap, $todayIndex) {
                $d = $dayMap[strtolower($s->day)] ?? 0;
                return $d >= $todayIndex ? $d : $d + 7;
            })
            ->take(6)
            ->map(fn($s) => [
                'day'     => $s->day,
                'period'  => $s->period?->name,
                'time'    => $this->formatTime($s->period?->start_time ?? '') . ' – ' . $this->formatTime($s->period?->end_time ?? ''),
                'subject' => $s->subject?->subject,
                'class'   => $s->setting?->schoolclass?->schoolclass,
                'room'    => $s->room?->room_name,
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
}
