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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TimetableController extends Controller
{
    const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    const DAYS_MAP = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5];

    /** Color palette for PDF day-column headers */
    const DAY_COLORS = [
        'Monday'    => '#1565C0',
        'Tuesday'   => '#6A1B9A',
        'Wednesday' => '#1B5E20',
        'Thursday'  => '#E65100',
        'Friday'    => '#880E4F',
    ];

    /** Subject background colors for PDF cells */
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
        $schoolterms    = Schoolterm::all();

        $subjectsWithTeachers = SubjectTeacher::with(['subject', 'staff'])
            ->get()
            ->map(fn($st) => [
                'subject_id'   => $st->subjectid,
                'subject_name' => $st->subject->subject ?? 'Unknown',
                'teacher_id'   => $st->staffid,
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

        $sessionId = $request->input('session_id')
            ?? Schoolsession::where('status', 'Current')->value('id')
            ?? Schoolsession::latest('id')->value('id');

        $termId = $request->input('term_id') ?? Schoolterm::latest('id')->value('id');

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

        $allPeriods = TimetablePeriod::whereIn(
            'setting_id',
            TimetableSetting::where('session_id', $sessionId)->pluck('id')
        )->orderBy('order')->get()->unique('order');

        $sessions       = Schoolsession::orderByDesc('id')->get();
        $terms          = Schoolterm::all();
        $upcomingSlots  = $this->getUpcomingSlots($teacherId, $sessionId);
        $weeklySummary  = $this->getWeeklySummary($teacherId, $sessionId);

        return view('timetable.teacher', compact(
            'pagetitle', 'slots', 'days', 'allPeriods', 'sessions', 'terms',
            'sessionId', 'termId', 'upcomingSlots', 'weeklySummary', 'teacherPicture'
        ));
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
                'school_day_start'              => '08:00',
                'school_day_end'                => '14:30',
                'period_duration_minutes'       => 40,
                'short_break_duration_minutes'  => 20,
                'long_break_duration_minutes'   => 40,
                'is_active'                     => true,
                'active_days'                   => self::DAYS,
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
            'periods', 'constraints.subject', 'schoolclass', 'session', 'term'
        ])->findOrFail($settingId);

        $availableSubjects = SubjectTeacher::where('sessionid', $setting->session_id)
            ->whereHas('subjectclass', fn($q) => $q->where('schoolclassid', $setting->schoolclass_id))
            ->with(['subject', 'staff'])
            ->get()
            ->map(fn($st) => [
                'subject_id'   => $st->subjectid,
                'subject_name' => $st->subject->subject ?? 'Unknown',
                'subject_code' => $st->subject->subject_code ?? '',
                'teacher_id'   => $st->staffid,
                'teacher_name' => $st->staff->name ?? 'Unknown',
            ]);

        return response()->json([
            'success'            => true,
            'setting'            => $setting,
            'available_subjects' => $availableSubjects,
        ]);
    }

    // =========================================================================
    // SAVE SETTINGS + AUTO-BUILD PERIODS
    // =========================================================================

    public function saveSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id'                    => 'required|exists:timetable_settings,id',
            'school_day_start'              => 'required|date_format:H:i',
            'school_day_end'                => 'required|date_format:H:i',
            'period_duration_minutes'       => 'required|integer|min:20|max:90',
            'short_break_duration_minutes'  => 'required|integer|min:5|max:60',
            'long_break_duration_minutes'   => 'required|integer|min:10|max:90',
            'active_days'                   => 'required|array|min:1',
            'periods'                       => 'required|array|min:1',
            'periods.*.name'                => 'required|string|max:60',
            'periods.*.type'                => 'required|in:lesson,short_break,long_break,assembly,free',
        ]);

        try {
            DB::beginTransaction();

            $setting = TimetableSetting::findOrFail($validated['setting_id']);
            $setting->update([
                'school_day_start'              => $validated['school_day_start'],
                'school_day_end'                => $validated['school_day_end'],
                'period_duration_minutes'       => $validated['period_duration_minutes'],
                'short_break_duration_minutes'  => $validated['short_break_duration_minutes'],
                'long_break_duration_minutes'   => $validated['long_break_duration_minutes'],
                'active_days'                   => $validated['active_days'],
            ]);

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
            'setting_id'                          => 'required|exists:timetable_settings,id',
            'constraints'                         => 'required|array',
            'constraints.*.subject_id'            => 'required|exists:subject,id',
            'constraints.*.periods_per_week'      => 'required|integer|min:1|max:10',
            'constraints.*.allow_double'          => 'boolean',
            'constraints.*.max_double'            => 'integer|min:0|max:5',
            'constraints.*.preferred_days'        => 'nullable|array',
            'constraints.*.avoid_days'            => 'nullable|array',
            'constraints.*.preferred_periods'     => 'nullable|array',
            'constraints.*.is_compulsory'         => 'boolean',
        ]);

        DB::transaction(function () use ($validated) {
            TimetableConstraint::where('setting_id', $validated['setting_id'])->delete();
            foreach ($validated['constraints'] as $c) {
                TimetableConstraint::create([
                    'setting_id'                    => $validated['setting_id'],
                    'subject_id'                    => $c['subject_id'],
                    'periods_per_week'              => $c['periods_per_week'],
                    'allow_double_period'           => $c['allow_double'] ?? false,
                    'max_double_periods_per_week'   => $c['max_double'] ?? 1,
                    'preferred_days'                => $c['preferred_days'] ?? null,
                    'avoid_days'                    => $c['avoid_days'] ?? null,
                    'preferred_periods'             => $c['preferred_periods'] ?? null,
                    'is_compulsory'                 => $c['is_compulsory'] ?? true,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // TEACHER AVAILABILITY
    // =========================================================================

    public function saveTeacherAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id'                    => 'required|exists:users,id',
            'availability'                  => 'required|array',
            'availability.*.day'            => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'availability.*.start_time'     => 'required|date_format:H:i',
            'availability.*.end_time'       => 'required|date_format:H:i|after:start_time',
            'availability.*.is_available'   => 'boolean',
        ]);

        foreach ($validated['availability'] as $avail) {
            TeacherAvailability::updateOrCreate(
                ['teacher_id' => $validated['teacher_id'], 'day' => $avail['day']],
                [
                    'start_time'   => $avail['start_time'],
                    'end_time'     => $avail['end_time'],
                    'is_available' => $avail['is_available'] ?? true,
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    public function getTeacherAvailability(int $teacherId): JsonResponse
    {
        $availability = TeacherAvailability::where('teacher_id', $teacherId)->get();
        return response()->json(['success' => true, 'availability' => $availability]);
    }

    // =========================================================================
    // SMART AUTO-GENERATE
    // =========================================================================

    public function autoGenerate(Request $request): JsonResponse
    {
        $validated = $request->validate(['setting_id' => 'required|exists:timetable_settings,id']);

        try {
            DB::beginTransaction();

            $setting      = TimetableSetting::with(['periods', 'constraints.subject'])->findOrFail($validated['setting_id']);
            $lessonPeriods = $setting->periods->where('type', 'lesson')->values();
            $days          = $setting->active_days ?? self::DAYS;
            $constraints   = $setting->constraints->keyBy('subject_id');
            $classId       = $setting->schoolclass_id;
            $sessionId     = $setting->session_id;

            $subjectTeachers = SubjectTeacher::where('sessionid', $sessionId)
                ->whereHas('subjectclass', fn($q) => $q->where('schoolclassid', $classId))
                ->with(['subject', 'staff'])
                ->get()
                ->groupBy('subjectid');

            $teacherAvailability = [];
            foreach ($subjectTeachers as $subjectId => $teachers) {
                foreach ($teachers as $teacher) {
                    if ($teacher->staffid) {
                        $teacherAvailability[$teacher->staffid] = TeacherAvailability::where('teacher_id', $teacher->staffid)->get();
                    }
                }
            }

            TimetableSlot::where('setting_id', $setting->id)->delete();

            $slotPool   = $this->buildWeightedSlotPool($days, $lessonPeriods);
            $teacherDaySlot = [];
            $placed = [];

            $requirements = $constraints->sortByDesc(fn($c) => ($c->is_compulsory ? 100 : 0) + $c->periods_per_week)->values();

            foreach ($requirements as $constraint) {
                $subjectId   = $constraint->subject_id;
                $needed      = $constraint->periods_per_week;
                $allowDouble = $constraint->allow_double_period;
                $maxDouble   = $constraint->max_double_periods_per_week;
                $preferDays  = $constraint->preferred_days ?? [];
                $avoidDays   = $constraint->avoid_days ?? [];
                $preferPeriods = $constraint->preferred_periods ?? [];
                $doubleCount = 0;

                $teacherEntry = $subjectTeachers->get($subjectId)?->first();
                $teacherId    = $teacherEntry?->staffid;

                $scoredSlots = [];
                foreach ($slotPool as $slot) {
                    $day      = $slot['day'];
                    $periodId = $slot['period_id'];
                    $key      = $day . '_' . $periodId;

                    if (isset($placed[$key])) continue;

                    // Teacher availability check
                    if ($teacherId && isset($teacherAvailability[$teacherId])) {
                        $avail = $teacherAvailability[$teacherId]->firstWhere('day', $day);
                        if ($avail && !$avail->is_available) continue;
                    }

                    // Teacher conflict check
                    if ($teacherId) {
                        $teacherDaySlot[$teacherId][$day] ??= [];
                        if (in_array($periodId, $teacherDaySlot[$teacherId][$day])) continue;
                    }

                    $score = 0;
                    if (in_array($day, $preferDays)) $score += 10;
                    if (in_array($day, $avoidDays)) $score -= 10;
                    if (in_array((string)$slot['period_order'], $preferPeriods)) $score += 5;

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
                    }
                    $placedThisSubject++;

                    // Try double period
                    if ($allowDouble && $doubleCount < $maxDouble && $placedThisSubject < $needed) {
                        $nextPeriod = $this->getNextLessonPeriod($lessonPeriods, $periodId);
                        if ($nextPeriod) {
                            $nextKey        = $day . '_' . $nextPeriod->id;
                            $teacherConflict = $teacherId && in_array($nextPeriod->id, $teacherDaySlot[$teacherId][$day] ?? []);
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
                                }
                                $placedThisSubject++;
                                $doubleCount++;
                            }
                        }
                    }
                }
            }

            // Mark remaining lesson slots as free
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
            Log::error('autoGenerate failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
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
                'id'            => $slot->id,
                'subject_id'    => $slot->subject_id,
                'subject'       => $slot->subject?->subject,
                'subject_code'  => $slot->subject?->subject_code,
                'teacher_id'    => $slot->teacher_id,
                'teacher'       => $slot->teacher?->name,
                'teacher_picture' => $teacherPicture,
                'teacher_email' => $slot->teacher?->email,
                'room'          => $slot->room,
                'is_double'     => $slot->is_double,
                'is_free'       => $slot->is_free,
                'notes'         => $slot->notes,
            ];
        }

        $allTeachers = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))
            ->with('staffPicture')
            ->get()
            ->map(fn($t) => [
                'id'      => $t->id,
                'name'    => $t->name,
                'email'   => $t->email,
                'picture' => $t->staffPicture
                    ? asset('storage/staff_avatars/' . $t->staffPicture->picture)
                    : asset('storage/staff_avatars/default.png'),
            ]);

        return response()->json([
            'success'  => true,
            'setting'  => $setting,
            'periods'  => $setting->periods,
            'grid'     => $grid,
            'days'     => $setting->active_days ?? self::DAYS,
            'teachers' => $allTeachers,
        ]);
    }

    // =========================================================================
    // SAVE SLOT — FIXED conflict check scope
    // =========================================================================

    public function saveSlot(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id' => 'required|exists:timetable_settings,id',
            'period_id'  => 'required|exists:timetable_periods,id',
            'day'        => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'subject_id' => 'nullable|exists:subject,id',
            'teacher_id' => 'nullable|exists:users,id',
            'room'       => 'nullable|string|max:100',
            'is_double'  => 'boolean',
            'is_free'    => 'boolean',
            'notes'      => 'nullable|string|max:191',
        ]);

        // Check cross-class teacher conflict within the same session
        if (!empty($validated['teacher_id'])) {
            $sessionId = TimetableSetting::where('id', $validated['setting_id'])->value('session_id');

            $conflict = TimetableSlot::where('period_id', $validated['period_id'])
                ->where('day', $validated['day'])
                ->where('teacher_id', $validated['teacher_id'])
                ->where('setting_id', '!=', $validated['setting_id'])
                ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId)->where('is_active', true))
                ->exists();

            if ($conflict) {
                return response()->json([
                    'success'  => false,
                    'message'  => 'This teacher is already assigned to another class at this time.',
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
    // BULK UPDATE
    // =========================================================================

    public function bulkUpdateSlots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setting_id'              => 'required|exists:timetable_settings,id',
            'updates'                 => 'required|array',
            'updates.*.period_id'     => 'required|exists:timetable_periods,id',
            'updates.*.day'           => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'updates.*.subject_id'    => 'nullable|exists:subject,id',
            'updates.*.teacher_id'    => 'nullable|exists:users,id',
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
    // GET CLASS SUBJECTS
    // =========================================================================

    public function getClassSubjects(Request $request): JsonResponse
    {
        $subjectTeachers = SubjectTeacher::where('sessionid', $request->input('session_id'))
            ->when($request->input('term_id'), fn($q) => $q->where('termid', $request->input('term_id')))
            ->whereHas('subjectclass', fn($q) => $q->where('schoolclassid', $request->input('class_id')))
            ->with(['subject', 'staff', 'staff.staffPicture'])
            ->get()
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
            'slots.subject', 'slots.period',
            'schoolclass', 'session', 'term',
        ])->findOrFail($validated['setting_id']);

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
                    'time'    => ($s->period?->start_time ?? '') . ' – ' . ($s->period?->end_time ?? ''),
                    'subject' => $s->subject?->subject,
                    'room'    => $s->room,
                ])->toArray(),
                'type'      => $validated['type'],
                'generated' => now()->format('d M Y H:i'),
            ];

            try {
                Mail::to($teacher->email)->send(new TimetableNotificationMail($notifData));

                foreach ($teacherSlots as $slot) {
                    TimetableNotification::create([
                        'teacher_id'   => $teacherId,
                        'slot_id'      => $slot->id,
                        'type'         => $validated['type'],
                        'email'        => $teacher->email,
                        'scheduled_at' => now(),
                        'sent_at'      => now(),
                        'status'       => 'sent',
                        'payload'      => json_encode($notifData),
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
    // CONFLICT CHECKER — checks across all classes in the same session
    // =========================================================================

    public function checkConflicts(int $settingId): JsonResponse
    {
        $setting = TimetableSetting::findOrFail($settingId);

        $slots = TimetableSlot::whereHas('setting', fn($q) => $q->where('session_id', $setting->session_id)->where('is_active', true))
            ->whereNotNull('teacher_id')
            ->with(['period', 'subject', 'setting.schoolclass', 'teacher', 'teacher.staffPicture'])
            ->get();

        $conflicts      = [];
        $teacherSlotMap = [];

        foreach ($slots as $slot) {
            $key = $slot->teacher_id . '_' . $slot->day . '_' . $slot->period_id;
            if (isset($teacherSlotMap[$key])) {
                $conflicts[] = [
                    'type'           => 'teacher_conflict',
                    'day'            => $slot->day,
                    'period'         => $slot->period?->name,
                    'period_time'    => $slot->period ? ($slot->period->start_time . ' – ' . $slot->period->end_time) : '',
                    'teacher'        => $slot->teacher?->name,
                    'teacher_picture'=> $slot->teacher && $slot->teacher->staffPicture
                        ? asset('storage/staff_avatars/' . $slot->teacher->staffPicture->picture) : null,
                    'subject_a'      => $teacherSlotMap[$key]->subject?->subject,
                    'subject_b'      => $slot->subject?->subject,
                    'class_a'        => $teacherSlotMap[$key]->setting?->schoolclass?->schoolclass,
                    'class_b'        => $slot->setting?->schoolclass?->schoolclass,
                ];
            } else {
                $teacherSlotMap[$key] = $slot;
            }
        }

        return response()->json(['success' => true, 'conflicts' => $conflicts, 'conflict_count' => count($conflicts)]);
    }

    // =========================================================================
    // EXPORT — CSV + PDF (printable HTML that browser prints to PDF)
    // =========================================================================

    public function export(Request $request, int $settingId)
    {
        $format  = $request->input('format', 'csv');
        $setting = TimetableSetting::with(['periods', 'schoolclass', 'session', 'term'])->findOrFail($settingId);

        $slots = TimetableSlot::where('setting_id', $settingId)->with(['subject', 'teacher', 'period'])->get();

        $grid          = [];
        $subjectColors = [];
        $colorIdx      = 0;

        foreach ($slots as $slot) {
            if ($slot->subject_id && !isset($subjectColors[$slot->subject_id])) {
                $subjectColors[$slot->subject_id] = $colorIdx % count(self::SUBJECT_PALETTE);
                $colorIdx++;
            }
            $grid[$slot->period_id][$slot->day] = [
                'subject'      => $slot->subject?->subject ?? ($slot->is_free ? 'FREE' : '—'),
                'subject_code' => $slot->subject?->subject_code ?? '',
                'teacher'      => $slot->teacher?->name ?? '—',
                'is_free'      => $slot->is_free ?? !$slot->subject_id,
                'subject_id'   => $slot->subject_id,
            ];
        }

        $days        = $setting->active_days ?? self::DAYS;
        $periods     = $setting->periods;
        $className   = $setting->schoolclass->schoolclass ?? 'Class';
        $sessionName = $setting->session->session ?? 'Session';
        $termName    = $setting->term?->term ?? 'All Terms';

        return match($format) {
            'pdf'   => $this->exportPdf($setting, $periods, $days, $grid, $subjectColors, $className, $sessionName, $termName),
            default => $this->exportCsv($setting, $periods, $days, $grid, $className, $sessionName),
        };
    }

    private function exportCsv($setting, $periods, $days, $grid, $className, $sessionName)
    {
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', "timetable_{$className}_{$sessionName}.csv");
        $handle   = fopen('php://temp', 'w+');

        fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

        $header = ['Period', 'Time'];
        foreach ($days as $day) {
            $header[] = "{$day} (Subject)";
            $header[] = "{$day} (Teacher)";
        }
        fputcsv($handle, $header);

        foreach ($periods as $period) {
            $row = [$period->name, $period->start_time . ' – ' . $period->end_time];
            foreach ($days as $day) {
                $s = $grid[$period->id][$day] ?? ['subject' => '—', 'teacher' => '—'];
                $row[] = $s['subject'];
                $row[] = $s['teacher'];
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

    private function exportPdf($setting, $periods, $days, $grid, $subjectColors, $className, $sessionName, $termName)
    {
        $schoolName  = config('app.school_name', config('app.name', 'School'));
        $generatedAt = now()->format('d F Y, H:i');

        // Build subject color CSS classes
        $subjectColorCss = '';
        foreach ($subjectColors as $subjectId => $cIdx) {
            $bg = self::SUBJECT_PALETTE[$cIdx];
            $subjectColorCss .= ".subj-{$subjectId}{background:{$bg}!important}\n";
        }

        // Build day header cells
        $dayHeaders = '';
        $colPct     = round(80 / max(count($days), 1), 2);
        foreach ($days as $day) {
            $hc = self::DAY_COLORS[$day] ?? '#333';
            $dayHeaders .= "<th class=\"day-header\" style=\"background:{$hc};width:{$colPct}%\">" . htmlspecialchars($day) . "</th>";
        }

        // Build table rows
        $tableRows = '';
        foreach ($periods as $period) {
            $isBreak   = $period->is_break ?? false;
            $rowCls    = $isBreak ? ' class="break-row"' : '';
            $tableRows .= "<tr{$rowCls}>";
            $tableRows .= '<td class="period-cell"><div class="pname">' . htmlspecialchars($period->name)
                . '</div><div class="ptime">' . htmlspecialchars($period->start_time . ' – ' . $period->end_time) . '</div></td>';

            foreach ($days as $day) {
                $slot   = $grid[$period->id][$day] ?? null;
                $isFree = !$slot || ($slot['is_free'] ?? false) || $slot['subject'] === '—';
                $sid    = $slot['subject_id'] ?? null;
                $ccls   = ($sid && isset($subjectColors[$sid])) ? " subj-{$sid}" : '';

                if ($isBreak) {
                    $tableRows .= '<td class="break-cell"><span class="break-lbl">Break</span></td>';
                } elseif ($isFree) {
                    $tableRows .= '<td class="free-cell"><span class="free-lbl">Free</span></td>';
                } else {
                    $tableRows .= "<td class=\"slot-cell{$ccls}\">"
                        . '<div class="sname">' . htmlspecialchars($slot['subject'] ?? '—') . '</div>'
                        . (!empty($slot['subject_code']) ? '<div class="scode">' . htmlspecialchars($slot['subject_code']) . '</div>' : '')
                        . '<div class="tname">' . htmlspecialchars($slot['teacher'] ?? '—') . '</div>'
                        . '</td>';
                }
            }
            $tableRows .= '</tr>';
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Timetable — {$className}</title>
<style>
@page{size:A4 landscape;margin:12mm 10mm}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Arial,sans-serif;font-size:10px;color:#1a1a2e;background:#fff;-webkit-print-color-adjust:exact;print-color-adjust:exact}
.hdr{display:flex;align-items:center;justify-content:space-between;padding-bottom:8px;border-bottom:3px solid #1565C0;margin-bottom:10px}
.school{font-size:17px;font-weight:700;color:#1565C0}
.ttitle{font-size:13px;font-weight:700;color:#222;text-align:center}
.tmeta{font-size:9px;color:#666;text-align:center;margin-top:2px}
.ginfo{font-size:8px;color:#999;text-align:right}
table{width:100%;border-collapse:collapse;table-layout:fixed}
th,td{border:1px solid #dde1e7;vertical-align:middle;padding:4px 5px}
.period-header{background:#1a1a2e;color:#fff;font-size:10px;font-weight:600;text-align:center;width:72px}
.day-header{color:#fff;font-size:11px;font-weight:700;text-align:center}
.period-cell{background:#f0f2f5;text-align:center;width:72px}
.pname{font-weight:700;font-size:9px;color:#1a1a2e}
.ptime{font-size:8px;color:#666;margin-top:1px}
.slot-cell{text-align:center;padding:6px 4px}
.sname{font-weight:700;font-size:10px;color:#1a1a2e;margin-bottom:1px}
.scode{font-size:8px;color:#555;font-style:italic;margin-bottom:2px}
.tname{font-size:8px;color:#444}
.break-row td{height:20px}
.break-cell{background:#FFF8E1!important;text-align:center}
.break-lbl{font-size:8px;color:#F57F17;font-style:italic;font-weight:600}
.free-cell{background:#fafafa!important;text-align:center}
.free-lbl{font-size:8px;color:#bbb}
.footer{margin-top:8px;display:flex;justify-content:space-between;padding-top:6px;border-top:1px solid #dde1e7;font-size:8px;color:#888}
.print-btn{position:fixed;top:12px;right:12px;background:#1565C0;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;z-index:999}
.print-btn:hover{background:#0D47A1}
@media print{.print-btn{display:none!important}}
{$subjectColorCss}
</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">&#x1F5A8; Print / Save as PDF</button>
<div class="hdr">
  <div class="school">{$schoolName}</div>
  <div>
    <div class="ttitle">Class Timetable &mdash; {$className}</div>
    <div class="tmeta">{$sessionName} &nbsp;&bull;&nbsp; {$termName}</div>
  </div>
  <div class="ginfo">Generated: {$generatedAt}<br>Setting ID: {$setting->id}</div>
</div>
<table>
  <thead><tr><th class="period-header">Period</th>{$dayHeaders}</tr></thead>
  <tbody>{$tableRows}</tbody>
</table>
<div class="footer">
  <span>Auto-generated &mdash; for latest version refer to the school management system.</span>
  <span>&#x2588; Break &nbsp; &#x2591; Free period</span>
</div>
</body>
</html>
HTML;

        // Returns a printable HTML page. In the browser: File → Print → Save as PDF (landscape A4).
        // To generate a real PDF server-side, install dompdf (composer require dompdf/dompdf)
        // and uncomment the dompdf block below.
        return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');

        /*
        // ── dompdf (server-side PDF) ─────────────────────────────────────────
        // composer require dompdf/dompdf
        $dompdf = new \Dompdf\Dompdf(['enable_remote' => false]);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $fname = preg_replace('/[^A-Za-z0-9._-]/', '_', "timetable_{$className}_{$sessionName}.pdf");
        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$fname}\"");
        */
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
    // CLONE SETTING — FIXED: clones slots with remapped period IDs
    // =========================================================================

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
                $newC             = $constraint->replicate();
                $newC->setting_id = $newSetting->id;
                $newC->save();
            }

            foreach ($oldSetting->slots as $slot) {
                if (!isset($periodMap[$slot->period_id])) continue;
                $newSlot             = $slot->replicate();
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
    // SUBSTITUTE REQUESTS
    // =========================================================================

    public function requestSubstitute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slot_id'                => 'required|exists:timetable_slots,id',
            'substitute_teacher_id'  => 'required|exists:users,id',
            'reason'                 => 'required|string|max:500',
            'assignment_date'        => 'required|date|after_or_equal:today',
        ]);

        $slot = TimetableSlot::findOrFail($validated['slot_id']);
        if ($slot->teacher_id != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You can only request substitutes for your own classes'], 403);
        }

        $substitute = SubstituteAssignment::create([
            'original_teacher_id'    => Auth::id(),
            'substitute_teacher_id'  => $validated['substitute_teacher_id'],
            'slot_id'                => $slot->id,
            'assignment_date'        => $validated['assignment_date'],
            'reason'                 => $validated['reason'],
            'status'                 => 'pending',
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

        $slot = $substitute->slot;
        $originalName = $substitute->originalTeacher->name ?? 'Unknown';
        $slot->update([
            'teacher_id' => $substitute->substitute_teacher_id,
            'notes' => ($slot->notes ? $slot->notes . "\n" : '') . "[SUBSTITUTE] Original: {$originalName}, Date: {$substitute->assignment_date}",
        ]);

        return response()->json(['success' => true]);
    }

    public function getSubstituteRequests(Request $request): JsonResponse
    {
        $requests = SubstituteAssignment::with(['originalTeacher', 'substituteTeacher', 'slot.period', 'slot.subject'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date, fn($q) => $q->whereDate('assignment_date', $request->date))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json(['success' => true, 'requests' => $requests]);
    }

    // =========================================================================
    // WORKLOAD DASHBOARD
    // =========================================================================

    public function workloadDashboard(Request $request): JsonResponse
    {
        $sessionId = $request->session_id ?? Schoolsession::where('status', 'Current')->value('id');

        $teachers = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->with('staffPicture')->get();

        $workloadData = [];
        foreach ($teachers as $teacher) {
            $slots = TimetableSlot::where('teacher_id', $teacher->id)
                ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
                ->with(['setting.schoolclass', 'subject'])
                ->get();

            $dailyLoad = [];
            foreach (self::DAYS as $day) {
                $dailyLoad[$day] = $slots->where('day', $day)->count();
            }

            $workloadData[] = [
                'teacher_id'        => $teacher->id,
                'teacher_name'      => $teacher->name,
                'teacher_picture'   => $teacher->staffPicture ? asset('storage/staff_avatars/' . $teacher->staffPicture->picture) : null,
                'periods_assigned'  => $slots->count(),
                'classes_taught'    => $slots->pluck('setting.schoolclass.schoolclass')->filter()->unique()->values(),
                'subjects_taught'   => $slots->pluck('subject.subject')->filter()->unique()->values(),
                'daily_load'        => $dailyLoad,
            ];
        }

        usort($workloadData, fn($a, $b) => $b['periods_assigned'] - $a['periods_assigned']);

        return response()->json(['success' => true, 'workload' => $workloadData]);
    }

    // =========================================================================
    // PRIVATE HELPERS
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
        $dayMap     = ['monday' => 0, 'tuesday' => 1, 'wednesday' => 2, 'thursday' => 3, 'friday' => 4];
        $today      = strtolower(now()->format('l'));
        $todayIndex = $dayMap[$today] ?? 0;
        $now        = Carbon::now();

        $slots = TimetableSlot::where('teacher_id', $teacherId)
            ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId)->where('is_active', true))
            ->whereNotNull('subject_id')
            ->with(['period', 'subject', 'setting.schoolclass'])
            ->get();

        return $slots
            ->filter(function ($slot) use ($now, $today) {
                if (strtolower($slot->day) === $today) {
                    try {
                        return Carbon::createFromFormat('H:i:s', $slot->period->start_time)->greaterThan($now);
                    } catch (\Exception $e) { return true; }
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
                'time'    => ($s->period?->start_time ?? '') . ' – ' . ($s->period?->end_time ?? ''),
                'subject' => $s->subject?->subject,
                'class'   => $s->setting?->schoolclass?->schoolclass,
                'room'    => $s->room,
            ])
            ->values()->toArray();
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

        $slot = TimetableSlot::with(['period', 'subject'])->find($slotId);
        if (!$slot) return;

        TimetableNotification::create([
            'teacher_id'   => $teacherId,
            'slot_id'      => $slotId,
            'type'         => $type,
            'email'        => $teacher->email,
            'scheduled_at' => now(),
            'status'       => 'pending',
            'payload'      => json_encode(['day' => $slot->day, 'period' => $slot->period?->name, 'subject' => $slot->subject?->subject]),
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
