<?php
// app/Http/Controllers/TimetableController.php

namespace App\Http\Controllers;

use App\Mail\TimetableNotificationMail;
use App\Models\Room;
use App\Models\Schoolclass;
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
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TimetableController extends Controller
{
    const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    const DAYS_MAP = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5];

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
        $this->middleware('permission:Export timetable', ['only' => ['export']]);
        $this->middleware('permission:Request substitute', ['only' => ['requestSubstitute']]);
        $this->middleware('permission:Approve substitute', ['only' => ['approveSubstitute']]);
        $this->middleware('permission:View substitute requests', ['only' => ['getSubstituteRequests']]);
        $this->middleware('permission:Manage teacher availability', ['only' => ['saveTeacherAvailability', 'getTeacherAvailability']]);
        $this->middleware('permission:Check timetable conflicts', ['only' => ['checkConflicts']]);
        $this->middleware('permission:Send timetable notifications', ['only' => ['sendNotifications']]);
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index()
    {
        $pagetitle = 'Timetable Management';

        $schoolclasses = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm'])
            ->orderBy('schoolclass.schoolclass')
            ->get();

        $schoolsessions = Schoolsession::orderByDesc('id')->get();
        $schoolterms = Schoolterm::all();

        $subjectsWithTeachers = SubjectTeacher::with(['subject', 'staff'])
            ->get()
            ->map(fn($st) => [
                'subject_id' => $st->subjectid,
                'subject_name' => $st->subject->subject ?? 'Unknown',
                'teacher_id' => $st->staffid,
                'teacher_name' => $st->staff->name ?? 'Unknown',
            ]);

        $settings = TimetableSetting::with(['schoolclass', 'session', 'term'])
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->get();

        return view('timetable.index', compact(
            'pagetitle', 'schoolclasses', 'schoolsessions', 'schoolterms', 'settings', 'subjectsWithTeachers'
        ));
    }

    // =========================================================================
    // TEACHER VIEW
    // =========================================================================

    public function teacherView(Request $request)
    {
        $teacherId = Auth::id();
        $pagetitle = 'My Timetable';

        $sessionId = $request->input('session_id');
        $termId = $request->input('term_id');

        if (!$sessionId) {
            $sessionId = Schoolsession::where('status', 'Current')->value('id') ?? Schoolsession::latest('id')->value('id');
        }
        if (!$termId) {
            $termId = Schoolterm::latest('id')->value('id');
        }

        $teacherPicture = null;
        $teacher = User::with('staffPicture')->find($teacherId);
        if ($teacher && $teacher->staffPicture) {
            $teacherPicture = asset('storage/staff_avatars/' . $teacher->staffPicture->picture);
        }

        $slots = TimetableSlot::where('teacher_id', $teacherId)
            ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId)->where('is_active', true))
            ->with(['period', 'subject', 'setting.schoolclass', 'setting.term'])
            ->get()
            ->groupBy('day');

        $days = self::DAYS;

        $allPeriods = TimetablePeriod::whereIn('setting_id',
            TimetableSetting::where('session_id', $sessionId)->pluck('id')
        )->orderBy('order')->get()->unique('order');

        $sessions = Schoolsession::orderByDesc('id')->get();
        $terms = Schoolterm::all();

        // Get available substitute teachers (other teachers not busy at same time)
        $availableSubstitutes = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))
            ->where('id', '!=', $teacherId)
            ->select('id', 'name', 'email')
            ->get();

        $upcomingSlots = $this->getUpcomingSlots($teacherId, $sessionId);
        $weeklySummary = $this->getWeeklySummary($teacherId, $sessionId);

        return view('timetable.teacher', compact(
            'pagetitle', 'slots', 'days', 'allPeriods', 'sessions', 'terms',
            'sessionId', 'termId', 'upcomingSlots', 'weeklySummary',
            'teacherPicture', 'availableSubstitutes'
        ));
    }

    // =========================================================================
    // AVAILABLE SUBSTITUTES API (fixes missing route)
    // =========================================================================

    public function getAvailableSubstitutes(Request $request): JsonResponse
    {
        $slotId = $request->input('slot_id');

        if (!$slotId) {
            return response()->json(['teachers' => []]);
        }

        $slot = TimetableSlot::with('period')->find($slotId);
        if (!$slot) {
            return response()->json(['teachers' => []]);
        }

        // Find teachers who are NOT already assigned at the same day+period
        $busyTeacherIds = TimetableSlot::where('day', $slot->day)
            ->where('period_id', $slot->period_id)
            ->whereNotNull('teacher_id')
            ->where('id', '!=', $slot->id)
            ->pluck('teacher_id');

        $teachers = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))
            ->whereNotIn('id', $busyTeacherIds)
            ->where('id', '!=', $slot->teacher_id)
            ->select('id', 'name', 'email')
            ->get();

        return response()->json(['teachers' => $teachers]);
    }

    // =========================================================================
    // SETUP
    // =========================================================================

    public function setup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schoolclass_id' => 'required|exists:schoolclass,id',
            'session_id' => 'required|exists:schoolsession,id',
            'term_id' => 'nullable|exists:schoolterm,id',
        ]);

        $setting = TimetableSetting::firstOrCreate(
            array_filter($validated),
            [
                'school_day_start' => '08:00',
                'school_day_end' => '14:30',
                'period_duration_minutes' => 40,
                'short_break_duration_minutes' => 20,
                'long_break_duration_minutes' => 40,
                'is_active' => true,
                'active_days' => self::DAYS,
            ]
        );

        return response()->json(['success' => true, 'setting_id' => $setting->id, 'setting' => $setting]);
    }

    // =========================================================================
    // GET SETTING
    // =========================================================================

    public function getSetting(int $settingId): JsonResponse
    {
        $setting = TimetableSetting::with([
            'periods',
            'constraints.subject',
            'schoolclass',
            'session',
            'term'
        ])->findOrFail($settingId);

        $availableSubjects = SubjectTeacher::where('sessionid', $setting->session_id)
            ->whereHas('subjectclass', fn($q) => $q->where('schoolclassid', $setting->schoolclass_id))
            ->with(['subject', 'staff'])
            ->get()
            ->map(fn($st) => [
                'subject_id' => $st->subjectid,
                'subject_name' => $st->subject->subject ?? 'Unknown',
                'subject_code' => $st->subject->subject_code ?? '',
                'teacher_id' => $st->staffid,
                'teacher_name' => $st->staff->name ?? 'Unknown',
            ]);

        return response()->json([
            'success' => true,
            'setting' => $setting,
            'available_subjects' => $availableSubjects
        ]);
    }

    // =========================================================================
    // SAVE SETTINGS
    // =========================================================================

    public function saveSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id' => 'required|exists:timetable_settings,id',
            'school_day_start' => 'required|date_format:H:i',
            'school_day_end' => 'required|date_format:H:i',
            'period_duration_minutes' => 'required|integer|min:20|max:90',
            'short_break_duration_minutes' => 'required|integer|min:5|max:60',
            'long_break_duration_minutes' => 'required|integer|min:10|max:90',
            'active_days' => 'required|array|min:1',
            'periods' => 'required|array|min:1',
            'periods.*.name' => 'required|string|max:60',
            'periods.*.type' => 'required|in:lesson,short_break,long_break,assembly,free',
        ]);

        try {
            DB::beginTransaction();

            $setting = TimetableSetting::findOrFail($validated['setting_id']);
            $setting->update([
                'school_day_start' => $validated['school_day_start'],
                'school_day_end' => $validated['school_day_end'],
                'period_duration_minutes' => $validated['period_duration_minutes'],
                'short_break_duration_minutes' => $validated['short_break_duration_minutes'],
                'long_break_duration_minutes' => $validated['long_break_duration_minutes'],
                'active_days' => $validated['active_days'],
            ]);

            TimetablePeriod::where('setting_id', $setting->id)->delete();
            $start = Carbon::createFromFormat('H:i', $validated['school_day_start']);
            $order = 0;

            foreach ($validated['periods'] as $p) {
                $order++;
                $duration = match($p['type']) {
                    'short_break' => $validated['short_break_duration_minutes'],
                    'long_break' => $validated['long_break_duration_minutes'],
                    default => $validated['period_duration_minutes'],
                };

                $end = (clone $start)->addMinutes($duration);

                TimetablePeriod::create([
                    'setting_id' => $setting->id,
                    'order' => $order,
                    'name' => $p['name'],
                    'type' => $p['type'],
                    'start_time' => $start->format('H:i'),
                    'end_time' => $end->format('H:i'),
                    'duration_minutes' => $duration,
                    'is_break' => in_array($p['type'], ['short_break', 'long_break', 'assembly']),
                ]);

                $start = $end;
            }

            DB::commit();
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
            'setting_id' => 'required|exists:timetable_settings,id',
            'constraints' => 'required|array',
            'constraints.*.subject_id' => 'required|exists:subject,id',
            'constraints.*.periods_per_week' => 'required|integer|min:1|max:10',
            'constraints.*.allow_double' => 'boolean',
            'constraints.*.max_double' => 'integer|min:0|max:5',
            'constraints.*.preferred_days' => 'nullable|array',
            'constraints.*.avoid_days' => 'nullable|array',
            'constraints.*.preferred_periods' => 'nullable|array',
            'constraints.*.is_compulsory' => 'boolean',
        ]);

        DB::transaction(function () use ($validated) {
            TimetableConstraint::where('setting_id', $validated['setting_id'])->delete();
            foreach ($validated['constraints'] as $c) {
                TimetableConstraint::create([
                    'setting_id' => $validated['setting_id'],
                    'subject_id' => $c['subject_id'],
                    'periods_per_week' => $c['periods_per_week'],
                    'allow_double_period' => $c['allow_double'] ?? false,
                    'max_double_periods_per_week' => $c['max_double'] ?? 1,
                    'preferred_days' => $c['preferred_days'] ?? null,
                    'avoid_days' => $c['avoid_days'] ?? null,
                    'preferred_periods' => $c['preferred_periods'] ?? null,
                    'is_compulsory' => $c['is_compulsory'] ?? true,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // SAVE TEACHER AVAILABILITY
    // =========================================================================

    public function saveTeacherAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'availability' => 'required|array',
            'availability.*.day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'availability.*.start_time' => 'required|date_format:H:i',
            'availability.*.end_time' => 'required|date_format:H:i|after:start_time',
            'availability.*.is_available' => 'boolean',
        ]);

        foreach ($validated['availability'] as $avail) {
            TeacherAvailability::updateOrCreate(
                ['teacher_id' => $validated['teacher_id'], 'day' => $avail['day']],
                [
                    'start_time' => $avail['start_time'],
                    'end_time' => $avail['end_time'],
                    'is_available' => $avail['is_available'] ?? true,
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // GET TEACHER AVAILABILITY
    // =========================================================================

    public function getTeacherAvailability(int $teacherId): JsonResponse
    {
        $availability = TeacherAvailability::where('teacher_id', $teacherId)->get();
        return response()->json(['success' => true, 'availability' => $availability]);
    }

    // =========================================================================
    // AUTO-GENERATE
    // =========================================================================

    public function autoGenerate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id' => 'required|exists:timetable_settings,id',
        ]);

        try {
            DB::beginTransaction();

            $setting = TimetableSetting::with(['periods', 'constraints.subject'])->findOrFail($validated['setting_id']);
            $lessonPeriods = $setting->periods->where('type', 'lesson')->values();
            $days = $setting->active_days ?? self::DAYS;
            $constraints = $setting->constraints->keyBy('subject_id');

            $classId = $setting->schoolclass_id;
            $sessionId = $setting->session_id;

            $subjectTeachers = SubjectTeacher::where('sessionid', $sessionId)
                ->whereHas('subjectclass', fn($q) => $q->where('schoolclassid', $classId))
                ->with(['subject', 'staff'])
                ->get()
                ->groupBy('subjectid');

            $teacherAvailability = [];
            foreach ($subjectTeachers as $subjectId => $teachers) {
                foreach ($teachers as $teacher) {
                    if ($teacher->staffid) {
                        $avail = TeacherAvailability::where('teacher_id', $teacher->staffid)->get();
                        $teacherAvailability[$teacher->staffid] = $avail;
                    }
                }
            }

            TimetableSlot::where('setting_id', $setting->id)->delete();

            $slotPool = $this->buildWeightedSlotPool($days, $lessonPeriods);
            $teacherDaySlot = [];
            $teacherDayPeriodCount = [];
            $placed = [];

            $requirements = $constraints->sortByDesc(function ($c) {
                return ($c->is_compulsory ? 100 : 0) + $c->periods_per_week;
            })->values();

            foreach ($requirements as $constraint) {
                $subjectId = $constraint->subject_id;
                $needed = $constraint->periods_per_week;
                $allowDouble = $constraint->allow_double_period;
                $maxDouble = $constraint->max_double_periods_per_week;
                $preferDays = $constraint->preferred_days ?? [];
                $preferPeriods = $constraint->preferred_periods ?? [];
                $avoidDays = $constraint->avoid_days ?? [];

                $doubleCount = 0;
                $teacherEntry = $subjectTeachers->get($subjectId)?->first();
                $teacherId = $teacherEntry?->staffid;

                $scoredSlots = [];
                foreach ($slotPool as $slot) {
                    $day = $slot['day'];
                    $periodId = $slot['period_id'];
                    $periodOrder = $slot['period_order'];
                    $key = $day . '_' . $periodId;

                    if (isset($placed[$key])) continue;

                    if ($teacherId && isset($teacherAvailability[$teacherId])) {
                        $avail = $teacherAvailability[$teacherId]->firstWhere('day', $day);
                        if ($avail && !$avail->is_available) continue;
                    }

                    if ($teacherId) {
                        $teacherDaySlot[$teacherId][$day] ??= [];
                        if (in_array($periodId, $teacherDaySlot[$teacherId][$day])) continue;
                    }

                    $score = 0;
                    if (in_array($day, $preferDays)) $score += 10;
                    if (in_array($day, $avoidDays)) $score -= 10;
                    if (in_array((string)$periodOrder, $preferPeriods)) $score += 5;

                    $scoredSlots[] = ['slot' => $slot, 'score' => $score, 'key' => $key];
                }

                usort($scoredSlots, fn($a, $b) => $b['score'] - $a['score']);

                $placedThisSubject = 0;
                foreach ($scoredSlots as $scored) {
                    if ($placedThisSubject >= $needed) break;

                    $slot = $scored['slot'];
                    $day = $slot['day'];
                    $periodId = $slot['period_id'];
                    $key = $scored['key'];

                    if (isset($placed[$key])) continue;

                    TimetableSlot::create([
                        'setting_id' => $setting->id,
                        'period_id' => $periodId,
                        'day' => $day,
                        'subject_id' => $subjectId,
                        'teacher_id' => $teacherId,
                        'is_double' => false,
                        'is_free' => false,
                    ]);

                    $placed[$key] = $subjectId;
                    if ($teacherId) {
                        $teacherDaySlot[$teacherId][$day][] = $periodId;
                        $teacherDayPeriodCount[$teacherId][$day] = ($teacherDayPeriodCount[$teacherId][$day] ?? 0) + 1;
                    }
                    $placedThisSubject++;

                    if ($allowDouble && $doubleCount < $maxDouble && $placedThisSubject < $needed) {
                        $nextPeriod = $this->getNextLessonPeriod($lessonPeriods, $periodId);
                        if ($nextPeriod) {
                            $nextKey = $day . '_' . $nextPeriod->id;
                            if (!isset($placed[$nextKey])) {
                                $teacherConflict = $teacherId && in_array($nextPeriod->id, $teacherDaySlot[$teacherId][$day] ?? []);
                                if (!$teacherConflict) {
                                    TimetableSlot::create([
                                        'setting_id' => $setting->id,
                                        'period_id' => $nextPeriod->id,
                                        'day' => $day,
                                        'subject_id' => $subjectId,
                                        'teacher_id' => $teacherId,
                                        'is_double' => true,
                                        'is_free' => false,
                                    ]);
                                    $placed[$nextKey] = $subjectId;
                                    if ($teacherId) {
                                        $teacherDaySlot[$teacherId][$day][] = $nextPeriod->id;
                                        $teacherDayPeriodCount[$teacherId][$day]++;
                                    }
                                    $placedThisSubject++;
                                    $doubleCount++;
                                }
                            }
                        }
                    }
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

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Timetable generated successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('autoGenerate failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function buildWeightedSlotPool($days, $lessonPeriods): array
    {
        $pool = [];
        foreach ($days as $day) {
            foreach ($lessonPeriods as $period) {
                $pool[] = [
                    'day' => $day,
                    'period_id' => $period->id,
                    'period_order' => $period->order,
                ];
            }
        }
        return $pool;
    }

    // =========================================================================
    // GET GRID
    // =========================================================================

    public function getGrid(int $settingId): JsonResponse
    {
        $setting = TimetableSetting::with(['periods', 'schoolclass', 'session', 'term'])->findOrFail($settingId);

        $slots = TimetableSlot::where('setting_id', $settingId)
            ->with(['subject', 'teacher', 'teacher.staffPicture', 'period'])
            ->get();

        $grid = [];
        foreach ($slots as $slot) {
            $teacherPicture = null;
            if ($slot->teacher && $slot->teacher->staffPicture) {
                $teacherPicture = asset('storage/staff_avatars/' . $slot->teacher->staffPicture->picture);
            }

            $grid[$slot->period_id][$slot->day] = [
                'id' => $slot->id,
                'subject_id' => $slot->subject_id,
                'subject' => $slot->subject?->subject,
                'subject_code' => $slot->subject?->subject_code,
                'teacher_id' => $slot->teacher_id,
                'teacher' => $slot->teacher?->name,
                'teacher_picture' => $teacherPicture,
                'teacher_email' => $slot->teacher?->email,
                'room' => $slot->room,
                'is_double' => $slot->is_double,
                'is_free' => $slot->is_free,
                'notes' => $slot->notes,
            ];
        }

        $allTeachers = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))
            ->with('staffPicture')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'email' => $t->email,
                'picture' => $t->staffPicture ? asset('storage/staff_avatars/' . $t->staffPicture->picture) : asset('storage/staff_avatars/default.png'),
            ]);

        return response()->json([
            'success' => true,
            'setting' => $setting,
            'periods' => $setting->periods,
            'grid' => $grid,
            'days' => $setting->active_days ?? self::DAYS,
            'teachers' => $allTeachers,
        ]);
    }

    // =========================================================================
    // SAVE SLOT
    // =========================================================================

    public function saveSlot(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id' => 'required|exists:timetable_settings,id',
            'period_id' => 'required|exists:timetable_periods,id',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'subject_id' => 'nullable|exists:subject,id',
            'teacher_id' => 'nullable|exists:users,id',
            'room' => 'nullable|string|max:100',
            'is_double' => 'boolean',
            'is_free' => 'boolean',
            'notes' => 'nullable|string|max:191',
        ]);

        if (!empty($validated['teacher_id'])) {
            $conflict = TimetableSlot::where('period_id', $validated['period_id'])
                ->where('day', $validated['day'])
                ->where('teacher_id', $validated['teacher_id'])
                ->where('setting_id', '!=', $validated['setting_id'])
                ->whereHas('setting', fn($q) => $q->where('session_id', function($sub) use ($validated) {
                    $sub->select('session_id')->from('timetable_settings')->where('id', $validated['setting_id']);
                }))
                ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher has a conflict at this time slot.',
                    'conflict' => true,
                ], 422);
            }
        }

        $slot = TimetableSlot::updateOrCreate(
            ['setting_id' => $validated['setting_id'], 'period_id' => $validated['period_id'], 'day' => $validated['day']],
            array_merge($validated, ['is_free' => empty($validated['subject_id'])])
        );

        $this->logTimetableChange(Auth::id(), 'update', 'TimetableSlot', $slot->id);

        if ($slot->wasChanged('teacher_id') && $slot->teacher_id) {
            $this->scheduleNotification($slot->teacher_id, $slot->id, 'change_alert');
        }

        return response()->json(['success' => true, 'slot' => $slot->load(['subject', 'teacher'])]);
    }

    // =========================================================================
    // BULK UPDATE SLOTS
    // =========================================================================

    public function bulkUpdateSlots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id' => 'required|exists:timetable_settings,id',
            'updates' => 'required|array',
            'updates.*.period_id' => 'required|exists:timetable_periods,id',
            'updates.*.day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'updates.*.subject_id' => 'nullable|exists:subject,id',
            'updates.*.teacher_id' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['updates'] as $update) {
                TimetableSlot::updateOrCreate(
                    [
                        'setting_id' => $validated['setting_id'],
                        'period_id' => $update['period_id'],
                        'day' => $update['day'],
                    ],
                    [
                        'subject_id' => $update['subject_id'] ?? null,
                        'teacher_id' => $update['teacher_id'] ?? null,
                        'is_free' => empty($update['subject_id']),
                    ]
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
    // GET CLASS SUBJECTS
    // =========================================================================

    public function getClassSubjects(Request $request): JsonResponse
    {
        $classId = $request->input('class_id');
        $sessionId = $request->input('session_id');
        $termId = $request->input('term_id');

        $subjectTeachers = SubjectTeacher::where('sessionid', $sessionId)
            ->when($termId, fn($q) => $q->where('termid', $termId))
            ->whereHas('subjectclass', fn($q) => $q->where('schoolclassid', $classId))
            ->with(['subject', 'staff', 'staff.staffPicture'])
            ->get()
            ->map(fn($st) => [
                'subject_id' => $st->subjectid,
                'subject_name' => $st->subject?->subject,
                'subject_code' => $st->subject?->subject_code,
                'teacher_id' => $st->staffid,
                'teacher_name' => $st->staff?->name,
                'teacher_picture' => $st->staff && $st->staff->staffPicture
                    ? asset('storage/staff_avatars/' . $st->staff->staffPicture->picture)
                    : asset('storage/staff_avatars/default.png'),
            ]);

        return response()->json(['success' => true, 'data' => $subjectTeachers]);
    }

    // =========================================================================
    // SEND NOTIFICATIONS
    // =========================================================================

    public function sendNotifications(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id' => 'required|exists:timetable_settings,id',
            'type' => 'required|in:daily_summary,weekly_preview,change_alert',
        ]);

        $setting = TimetableSetting::with([
            'slots.teacher', 'slots.teacher.staffPicture',
            'slots.subject', 'slots.period', 'schoolclass', 'session', 'term'
        ])->findOrFail($validated['setting_id']);

        $byTeacher = $setting->slots->whereNotNull('teacher_id')->groupBy('teacher_id');
        $sent = 0;

        foreach ($byTeacher as $teacherId => $teacherSlots) {
            $teacher = $teacherSlots->first()->teacher;
            if (!$teacher || !$teacher->email) continue;

            $teacherPicture = $teacher->staffPicture
                ? asset('storage/staff_avatars/' . $teacher->staffPicture->picture)
                : null;

            $notifData = [
                'teacher' => $teacher->name,
                'teacher_picture' => $teacherPicture,
                'class' => $setting->schoolclass->schoolclass ?? '',
                'session' => $setting->session->session ?? '',
                'term' => $setting->term?->term ?? '',
                'slots' => $teacherSlots->map(fn($s) => [
                    'day' => $s->day,
                    'period' => $s->period?->name,
                    'time' => $s->period?->start_time . ' - ' . $s->period?->end_time,
                    'subject' => $s->subject?->subject,
                    'room' => $s->room,
                ])->toArray(),
                'type' => $validated['type'],
                'generated' => now()->format('d M Y H:i'),
            ];

            try {
                Mail::to($teacher->email)->send(new TimetableNotificationMail($notifData));

                foreach ($teacherSlots as $slot) {
                    TimetableNotification::create([
                        'teacher_id' => $teacherId,
                        'slot_id' => $slot->id,
                        'type' => $validated['type'],
                        'email' => $teacher->email,
                        'scheduled_at' => now(),
                        'sent_at' => now(),
                        'status' => 'sent',
                        'payload' => json_encode($notifData),
                    ]);
                }
                $sent++;
            } catch (\Exception $e) {
                Log::error('Timetable notification failed', ['teacher_id' => $teacherId, 'error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Notifications sent to {$sent} teacher(s).",
            'sent' => $sent,
        ]);
    }

    // =========================================================================
    // CONFLICT CHECKER
    // =========================================================================

    public function checkConflicts(int $settingId): JsonResponse
    {
        $slots = TimetableSlot::where('setting_id', $settingId)
            ->whereNotNull('teacher_id')
            ->with(['period', 'subject', 'setting.schoolclass', 'teacher', 'teacher.staffPicture'])
            ->get();

        $conflicts = [];
        $teacherSlotMap = [];

        foreach ($slots as $slot) {
            $key = $slot->teacher_id . '_' . $slot->day . '_' . $slot->period_id;
            if (isset($teacherSlotMap[$key])) {
                $teacherPicture = null;
                if ($slot->teacher && $slot->teacher->staffPicture) {
                    $teacherPicture = asset('storage/staff_avatars/' . $slot->teacher->staffPicture->picture);
                }
                $conflicts[] = [
                    'type' => 'teacher_conflict',
                    'day' => $slot->day,
                    'period' => $slot->period?->name,
                    'period_time' => ($slot->period?->start_time ?? '') . ' - ' . ($slot->period?->end_time ?? ''),
                    'teacher' => $slot->teacher?->name,
                    'teacher_picture' => $teacherPicture,
                    'subject_a' => $teacherSlotMap[$key]->subject?->subject,
                    'subject_b' => $slot->subject?->subject,
                    'class_a' => $teacherSlotMap[$key]->setting?->schoolclass?->schoolclass,
                    'class_b' => $slot->setting?->schoolclass?->schoolclass,
                ];
            } else {
                $teacherSlotMap[$key] = $slot;
            }
        }

        return response()->json([
            'success' => true,
            'conflicts' => $conflicts,
            'conflict_count' => count($conflicts),
        ]);
    }

    // =========================================================================
    // EXPORT — CSV and PDF
    // =========================================================================

    public function export(Request $request, int $settingId)
    {
        $format = $request->input('format', 'csv');

        $setting = TimetableSetting::with(['periods', 'schoolclass', 'session', 'term'])->findOrFail($settingId);
        $slots = TimetableSlot::where('setting_id', $settingId)
            ->with(['subject', 'teacher', 'period'])
            ->get();

        $grid = [];
        foreach ($slots as $slot) {
            $grid[$slot->period_id][$slot->day] = [
                'subject' => $slot->subject?->subject ?? ($slot->is_free ? 'FREE' : '—'),
                'teacher' => $slot->teacher?->name ?? '—',
            ];
        }

        $days = $setting->active_days ?? self::DAYS;
        $periods = $setting->periods;
        $className = $setting->schoolclass->schoolclass ?? 'Class';
        $sessionName = $setting->session->session ?? 'Session';
        $termName = $setting->term?->term ?? 'All Terms';

        if ($format === 'pdf') {
            return $this->exportToPdf($setting, $periods, $days, $grid, $className, $sessionName, $termName);
        }

        // Default: CSV
        $filename = "timetable_{$className}_{$sessionName}.csv";
        $handle = fopen('php://temp', 'w+');

        // School info header
        fputcsv($handle, ['SCHOOL TIMETABLE']);
        fputcsv($handle, ['Class:', $className, 'Session:', $sessionName, 'Term:', $termName]);
        fputcsv($handle, ['Generated:', now()->format('d M Y H:i')]);
        fputcsv($handle, []);

        $header = ['Period', 'Time'];
        foreach ($days as $day) {
            $header[] = $day;
        }
        fputcsv($handle, $header);

        foreach ($periods as $period) {
            $row = [
                $period->name,
                $period->start_time . ' - ' . $period->end_time,
            ];
            foreach ($days as $day) {
                $slot = $grid[$period->id][$day] ?? ['subject' => '—', 'teacher' => '—'];
                $row[] = $slot['subject'] . ' / ' . $slot['teacher'];
            }
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Generate a clean HTML-based PDF using DomPDF (or Snappy/MPDF if installed).
     * Falls back to a clean printable HTML page if no PDF library is available.
     */
    private function exportToPdf($setting, $periods, $days, $grid, $className, $sessionName, $termName)
    {
        $html = $this->buildTimetableHtml($setting, $periods, $days, $grid, $className, $sessionName, $termName);

        // Try DomPDF (laravel-dompdf package)
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                ->setPaper('a4', 'landscape');
            $filename = "timetable_{$className}_{$sessionName}.pdf";
            return $pdf->download($filename);
        }

        // Try Snappy (knp-snappy package)
        if (class_exists(\Knp\Snappy\Pdf::class)) {
            $snappy = app('snappy.pdf.html');
            $filename = "timetable_{$className}_{$sessionName}.pdf";
            return response($snappy->getOutputFromHtml($html), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        }

        // Fallback: return as a printable HTML page
        return response($html, 200)
            ->header('Content-Type', 'text/html');
    }

    private function buildTimetableHtml($setting, $periods, $days, $grid, $className, $sessionName, $termName): string
    {
        $colCount = count($days) + 2; // period + time + days
        $colWidth = round(100 / $colCount, 2);

        $dayHeaders = '';
        foreach ($days as $day) {
            $dayHeaders .= "<th style='background:#1a1a2e;color:#fff;padding:8px 4px;text-align:center;font-size:11px;'>" . htmlspecialchars($day) . "</th>";
        }

        $rows = '';
        foreach ($periods as $period) {
            $isBreak = $period->is_break;
            $bgColor = $isBreak ? '#fff8e1' : '#ffffff';
            $cells = '';
            foreach ($days as $day) {
                $slot = $grid[$period->id][$day] ?? null;
                $subject = $slot['subject'] ?? '—';
                $teacher = $slot['teacher'] ?? '';
                $isFree = ($subject === 'FREE' || $subject === '—') && !$teacher;

                if ($isBreak) {
                    $cells .= "<td style='text-align:center;color:#888;font-size:10px;padding:6px 2px;'>Break</td>";
                } elseif ($isFree) {
                    $cells .= "<td style='text-align:center;color:#ccc;font-size:10px;padding:6px 2px;'>—</td>";
                } else {
                    $cells .= "<td style='text-align:center;padding:5px 3px;vertical-align:middle;'>
                        <div style='font-weight:600;font-size:10px;color:#1a1a2e;'>" . htmlspecialchars($subject) . "</div>
                        <div style='font-size:9px;color:#555;margin-top:2px;'>" . htmlspecialchars($teacher) . "</div>
                    </td>";
                }
            }

            $rows .= "<tr style='background:{$bgColor};border-bottom:1px solid #e8e8e8;'>
                <td style='font-weight:600;font-size:10px;padding:6px 6px;white-space:nowrap;color:#1a1a2e;'>" . htmlspecialchars($period->name) . "</td>
                <td style='font-size:9px;color:#666;padding:6px 4px;white-space:nowrap;'>" . htmlspecialchars($period->start_time . ' - ' . $period->end_time) . "</td>
                {$cells}
            </tr>";
        }

        return "<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Timetable - {$className}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; background: #fff; }
  .header { text-align: center; padding: 16px; border-bottom: 3px solid #1a1a2e; margin-bottom: 16px; }
  .header h1 { font-size: 18px; color: #1a1a2e; margin-bottom: 4px; }
  .header .meta { font-size: 11px; color: #555; }
  table { width: 100%; border-collapse: collapse; table-layout: fixed; }
  th, td { border: 1px solid #ddd; vertical-align: middle; }
  th { font-weight: 600; }
  .footer { margin-top: 16px; text-align: right; font-size: 9px; color: #aaa; }
  @media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none; }
  }
</style>
</head>
<body>
  <div class='header'>
    <h1>Class Timetable — " . htmlspecialchars($className) . "</h1>
    <div class='meta'>Session: " . htmlspecialchars($sessionName) . " &nbsp;|&nbsp; Term: " . htmlspecialchars($termName) . " &nbsp;|&nbsp; Generated: " . now()->format('d M Y') . "</div>
  </div>
  <table>
    <thead>
      <tr>
        <th style='background:#1a1a2e;color:#fff;padding:8px 6px;font-size:11px;width:80px;'>Period</th>
        <th style='background:#1a1a2e;color:#fff;padding:8px 4px;font-size:11px;width:80px;'>Time</th>
        {$dayHeaders}
      </tr>
    </thead>
    <tbody>
      {$rows}
    </tbody>
  </table>
  <div class='footer'>Printed on " . now()->format('d M Y H:i') . "</div>
  <script>
    if (typeof window !== 'undefined' && !window.location.search.includes('pdf')) {
      window.onload = function() { window.print(); }
    }
  </script>
</body>
</html>";
    }

    // =========================================================================
    // DELETE SETTING
    // =========================================================================

    public function deleteSetting(int $settingId): JsonResponse
    {
        $setting = TimetableSetting::findOrFail($settingId);
        $this->logTimetableChange(Auth::id(), 'delete', 'TimetableSetting', $settingId, null, $setting->toArray());
        $setting->delete();
        return response()->json(['success' => true]);
    }

    // =========================================================================
    // CLONE SETTING
    // =========================================================================

    public function cloneSetting(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id' => 'required|exists:timetable_settings,id',
            'new_session_id' => 'nullable|exists:schoolsession,id',
            'new_term_id' => 'nullable|exists:schoolterm,id',
        ]);

        $oldSetting = TimetableSetting::with(['periods', 'constraints', 'slots'])->findOrFail($validated['setting_id']);

        DB::beginTransaction();
        try {
            $newSetting = $oldSetting->replicate();
            $newSetting->session_id = $validated['new_session_id'] ?? $oldSetting->session_id;
            $newSetting->term_id = $validated['new_term_id'] ?? $oldSetting->term_id;
            $newSetting->save();

            // Map old period IDs to new ones for slot cloning
            $periodMap = [];
            foreach ($oldSetting->periods as $period) {
                $newPeriod = $period->replicate();
                $newPeriod->setting_id = $newSetting->id;
                $newPeriod->save();
                $periodMap[$period->id] = $newPeriod->id;
            }

            foreach ($oldSetting->constraints as $constraint) {
                $newConstraint = $constraint->replicate();
                $newConstraint->setting_id = $newSetting->id;
                $newConstraint->save();
            }

            // Clone slots with remapped period IDs
            foreach ($oldSetting->slots as $slot) {
                if (isset($periodMap[$slot->period_id])) {
                    $newSlot = $slot->replicate();
                    $newSlot->setting_id = $newSetting->id;
                    $newSlot->period_id = $periodMap[$slot->period_id];
                    $newSlot->save();
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'setting_id' => $newSetting->id]);
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
            'slot_id' => 'required|exists:timetable_slots,id',
            'substitute_teacher_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:500',
            'assignment_date' => 'required|date|after_or_equal:today',
        ]);

        $slot = TimetableSlot::findOrFail($validated['slot_id']);

        if ($slot->teacher_id != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You can only request substitutes for your own classes'], 403);
        }

        // Check substitute isn't already busy at that slot/date
        $substituteId = $validated['substitute_teacher_id'];
        $dayOfWeek = Carbon::parse($validated['assignment_date'])->format('l');

        if ($dayOfWeek !== $slot->day) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment date must fall on a ' . $slot->day,
            ], 422);
        }

        $alreadyBusy = TimetableSlot::where('teacher_id', $substituteId)
            ->where('period_id', $slot->period_id)
            ->where('day', $slot->day)
            ->exists();

        if ($alreadyBusy) {
            return response()->json([
                'success' => false,
                'message' => 'Selected substitute teacher is already teaching at this time.',
            ], 422);
        }

        $substitute = SubstituteAssignment::create([
            'original_teacher_id' => Auth::id(),
            'substitute_teacher_id' => $substituteId,
            'slot_id' => $slot->id,
            'assignment_date' => $validated['assignment_date'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'substitute' => $substitute]);
    }

    public function approveSubstitute(Request $request, int $substituteId): JsonResponse
    {
        $substitute = SubstituteAssignment::findOrFail($substituteId);

        if (!Auth::user()->can('Approve substitute')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $substitute->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $slot = $substitute->slot;
        $originalTeacherName = $substitute->originalTeacher->name ?? 'Unknown';
        $slot->update([
            'teacher_id' => $substitute->substitute_teacher_id,
            'notes' => ($slot->notes ? $slot->notes . "\n" : '') .
                       "[SUBSTITUTE] Original: {$originalTeacherName}, Date: {$substitute->assignment_date}",
        ]);

        return response()->json(['success' => true]);
    }

    public function getSubstituteRequests(Request $request): JsonResponse
    {
        $query = SubstituteAssignment::with(['originalTeacher', 'substituteTeacher', 'slot.period', 'slot.subject'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date, fn($q) => $q->whereDate('assignment_date', $request->date))
            ->orderBy('created_at', 'desc');

        $requests = $query->paginate($request->per_page ?? 20);

        return response()->json(['success' => true, 'requests' => $requests]);
    }

    // =========================================================================
    // WORKLOAD DASHBOARD
    // =========================================================================

    public function workloadDashboard(Request $request): JsonResponse
    {
        $sessionId = $request->session_id ?? Schoolsession::where('status', 'Current')->value('id');

        $teachers = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->get();

        $workloadData = [];
        foreach ($teachers as $teacher) {
            $periodsCount = TimetableSlot::where('teacher_id', $teacher->id)
                ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
                ->count();

            $classes = TimetableSlot::where('teacher_id', $teacher->id)
                ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
                ->with('setting.schoolclass')
                ->get()
                ->pluck('setting.schoolclass.schoolclass')
                ->unique()
                ->values();

            $subjects = TimetableSlot::where('teacher_id', $teacher->id)
                ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
                ->with('subject')
                ->get()
                ->pluck('subject.subject')
                ->filter()
                ->unique()
                ->values();

            $workloadData[] = [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name,
                'teacher_picture' => $teacher->staffPicture ? asset('storage/staff_avatars/' . $teacher->staffPicture->picture) : null,
                'periods_assigned' => $periodsCount,
                'classes_taught' => $classes,
                'subjects_taught' => $subjects,
            ];
        }

        return response()->json([
            'success' => true,
            'workload' => $workloadData,
        ]);
    }

    // =========================================================================
    // GENERATE ANALYTICS (was missing from original)
    // =========================================================================

    public function generateAnalytics(Request $request): JsonResponse
    {
        $sessionId = $request->session_id ?? Schoolsession::where('status', 'Current')->value('id');

        $totalSlots = TimetableSlot::whereHas('setting', fn($q) => $q->where('session_id', $sessionId))->count();
        $filledSlots = TimetableSlot::whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
            ->whereNotNull('subject_id')->count();
        $freeSlots = $totalSlots - $filledSlots;
        $completionRate = $totalSlots > 0 ? round(($filledSlots / $totalSlots) * 100, 1) : 0;

        $teacherCount = TimetableSlot::whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
            ->whereNotNull('teacher_id')
            ->distinct('teacher_id')
            ->count('teacher_id');

        $subjectDistribution = TimetableSlot::whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
            ->whereNotNull('subject_id')
            ->with('subject')
            ->get()
            ->groupBy('subject_id')
            ->map(fn($group) => [
                'subject' => $group->first()->subject?->subject ?? 'Unknown',
                'count' => $group->count(),
            ])
            ->values();

        $dayDistribution = [];
        foreach (self::DAYS as $day) {
            $dayDistribution[$day] = TimetableSlot::whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
                ->where('day', $day)
                ->whereNotNull('subject_id')
                ->count();
        }

        return response()->json([
            'success' => true,
            'analytics' => [
                'total_slots' => $totalSlots,
                'filled_slots' => $filledSlots,
                'free_slots' => $freeSlots,
                'completion_rate' => $completionRate,
                'active_teachers' => $teacherCount,
                'subject_distribution' => $subjectDistribution,
                'day_distribution' => $dayDistribution,
            ],
        ]);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function getNextLessonPeriod($lessonPeriods, int $currentPeriodId)
    {
        $found = false;
        foreach ($lessonPeriods as $p) {
            if ($found) return $p;
            if ($p->id === $currentPeriodId) $found = true;
        }
        return null;
    }

    private function getUpcomingSlots(int $teacherId, int $sessionId): array
    {
        $today = strtolower(now()->format('l'));
        $dayMap = ['monday' => 0, 'tuesday' => 1, 'wednesday' => 2, 'thursday' => 3, 'friday' => 4];
        $todayIndex = $dayMap[$today] ?? 0;

        $slots = TimetableSlot::where('teacher_id', $teacherId)
            ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId)->where('is_active', true))
            ->whereNotNull('subject_id')
            ->with(['period', 'subject', 'setting.schoolclass', 'setting.term'])
            ->get();

        $now = Carbon::now();

        return $slots
            ->filter(function ($slot) use ($now) {
                try {
                    $slotTime = Carbon::createFromFormat('H:i:s', $slot->period->start_time);
                    return $slotTime->greaterThan($now) || $slotTime->equalTo($now);
                } catch (\Exception $e) {
                    return false;
                }
            })
            ->sortBy(fn($s) => ($dayMap[strtolower($s->day)] ?? 0) >= $todayIndex
                ? ($dayMap[strtolower($s->day)] ?? 0)
                : ($dayMap[strtolower($s->day)] ?? 0) + 7
            )
            ->take(6)
            ->map(fn($s) => [
                'day' => $s->day,
                'period' => $s->period?->name,
                'time' => $s->period?->start_time . ' – ' . $s->period?->end_time,
                'subject' => $s->subject?->subject,
                'class' => $s->setting?->schoolclass?->schoolclass,
                'room' => $s->room,
            ])
            ->values()
            ->toArray();
    }

    private function getWeeklySummary(int $teacherId, int $sessionId): array
    {
        $slots = TimetableSlot::where('teacher_id', $teacherId)
            ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId)->where('is_active', true))
            ->whereNotNull('subject_id')
            ->with(['period', 'subject', 'setting.schoolclass'])
            ->get();

        $summary = [];
        foreach (self::DAYS as $day) {
            $daySlots = $slots->where('day', $day);
            $summary[$day] = [
                'count' => $daySlots->count(),
                'subjects' => $daySlots->pluck('subject.subject')->unique()->values()->toArray(),
            ];
        }
        return $summary;
    }

    private function scheduleNotification(int $teacherId, int $slotId, string $type): void
    {
        $teacher = User::find($teacherId);
        if (!$teacher || !$teacher->email) return;

        $slot = TimetableSlot::with(['period', 'subject'])->find($slotId);
        if (!$slot) return;

        TimetableNotification::create([
            'teacher_id' => $teacherId,
            'slot_id' => $slotId,
            'type' => $type,
            'email' => $teacher->email,
            'scheduled_at' => now(),
            'status' => 'pending',
            'payload' => json_encode([
                'day' => $slot->day,
                'period' => $slot->period?->name,
                'subject' => $slot->subject?->subject,
            ]),
        ]);
    }

    private function logTimetableChange(int $userId, string $action, string $modelType, ?int $modelId, $oldValues = null, $newValues = null): void
    {
        try {
            DB::table('timetable_audit_logs')->insert([
                'user_id' => $userId,
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'ip_address' => request()->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Audit log insert failed', ['error' => $e->getMessage()]);
        }
    }
}
