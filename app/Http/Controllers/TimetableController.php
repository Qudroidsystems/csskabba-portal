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

    // =========================================================================
    // INDEX — Admin Overview
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

        // Get subjects with teachers for dropdowns
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
    // TEACHER VIEW — Shows their own timetable
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

        // Get teacher's picture
        $teacherPicture = null;
        $teacher = User::with('staffPicture')->find($teacherId);
        if ($teacher && $teacher->staffPicture) {
            $teacherPicture = asset('storage/staff_avatars/' . $teacher->staffPicture->picture);
        }

        // Get slots for this teacher
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

        $upcomingSlots = $this->getUpcomingSlots($teacherId, $sessionId);
        $weeklySummary = $this->getWeeklySummary($teacherId, $sessionId);

        return view('timetable.teacher', compact(
            'pagetitle', 'slots', 'days', 'allPeriods', 'sessions', 'terms',
            'sessionId', 'termId', 'upcomingSlots', 'weeklySummary', 'teacherPicture'
        ));
    }

    // =========================================================================
    // SETUP — Creates or loads settings for a class
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

        // Get available subjects for this class
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
    // SAVE SETTINGS + AUTO-BUILD PERIODS
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

            // Rebuild periods
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
                [
                    'teacher_id' => $validated['teacher_id'],
                    'day' => $avail['day'],
                ],
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
    // SMART AUTO-GENERATE TIMETABLE
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

            // Get teachers for this class
            $classId = $setting->schoolclass_id;
            $sessionId = $setting->session_id;

            $subjectTeachers = SubjectTeacher::where('sessionid', $sessionId)
                ->whereHas('subjectclass', fn($q) => $q->where('schoolclassid', $classId))
                ->with(['subject', 'staff'])
                ->get()
                ->groupBy('subjectid');

            // Get teacher availability
            $teacherAvailability = [];
            foreach ($subjectTeachers as $subjectId => $teachers) {
                foreach ($teachers as $teacher) {
                    if ($teacher->staffid) {
                        $avail = TeacherAvailability::where('teacher_id', $teacher->staffid)->get();
                        $teacherAvailability[$teacher->staffid] = $avail;
                    }
                }
            }

            // Clear existing slots
            TimetableSlot::where('setting_id', $setting->id)->delete();

            // Build weighted slot pool
            $slotPool = $this->buildWeightedSlotPool($days, $lessonPeriods, $constraints, $subjectTeachers, $teacherAvailability);

            // Track allocations
            $subjectCount = [];
            $teacherDaySlot = [];
            $teacherDayPeriodCount = [];
            $placed = [];
            $doublePeriodsPlaced = [];

            // Sort requirements by priority (periods_per_week DESC, compulsory first)
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

                $placed[$subjectId] = 0;
                $doubleCount = 0;

                $teacherEntry = $subjectTeachers->get($subjectId)?->first();
                $teacherId = $teacherEntry?->staffid;

                // Score each slot for this subject
                $scoredSlots = [];
                foreach ($slotPool as $slot) {
                    $day = $slot['day'];
                    $periodId = $slot['period_id'];
                    $periodOrder = $slot['period_order'];
                    $key = $day . '_' . $periodId;

                    if (isset($placed[$key])) continue;

                    // Check teacher availability
                    if ($teacherId && isset($teacherAvailability[$teacherId])) {
                        $avail = $teacherAvailability[$teacherId]->firstWhere('day', $day);
                        if ($avail && !$avail->is_available) continue;
                    }

                    // Check teacher conflict
                    if ($teacherId) {
                        $teacherDaySlot[$teacherId][$day] ??= [];
                        if (in_array($periodId, $teacherDaySlot[$teacherId][$day])) continue;
                    }

                    // Calculate score
                    $score = 0;
                    if (in_array($day, $preferDays)) $score += 10;
                    if (in_array($day, $avoidDays)) $score -= 10;
                    if (in_array((string)$periodOrder, $preferPeriods)) $score += 5;

                    // Prefer spreading subjects across days
                    $currentDayCount = $teacherDayPeriodCount[$teacherId][$day] ?? 0;
                    if ($currentDayCount < 2) $score += 3;

                    $scoredSlots[] = ['slot' => $slot, 'score' => $score, 'key' => $key];
                }

                // Sort by score descending
                usort($scoredSlots, fn($a, $b) => $b['score'] - $a['score']);

                $placedThisSubject = 0;
                foreach ($scoredSlots as $scored) {
                    if ($placedThisSubject >= $needed) break;

                    $slot = $scored['slot'];
                    $day = $slot['day'];
                    $periodId = $slot['period_id'];
                    $key = $scored['key'];

                    if (isset($placed[$key])) continue;

                    // Place the slot
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

                    // Try double period on next consecutive lesson period
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

            // Mark remaining lesson slots as free
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

    private function buildWeightedSlotPool($days, $lessonPeriods, $constraints, $subjectTeachers, $teacherAvailability): array
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
    // GET TIMETABLE GRID (with teacher pictures for tooltips)
    // =========================================================================

    public function getGrid(int $settingId): JsonResponse
    {
        $setting = TimetableSetting::with(['periods', 'schoolclass', 'session', 'term'])->findOrFail($settingId);

        $slots = TimetableSlot::where('setting_id', $settingId)
            ->with(['subject', 'teacher', 'teacher.staffPicture', 'period'])
            ->get();

        // Build grid with teacher pictures
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

        // Get all teachers with their pictures for tooltips
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
    // SAVE SINGLE SLOT
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

        // Check teacher conflict
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

        // Log the change
        $this->logTimetableChange(Auth::id(), 'update', 'TimetableSlot', $slot->id);

        // Send notification if this is a change to an existing teacher's slot
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
    // GET SUBJECTS FOR A CLASS
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

        $setting = TimetableSetting::with(['slots.teacher', 'slots.teacher.staffPicture', 'slots.subject', 'slots.period', 'schoolclass', 'session', 'term'])
            ->findOrFail($validated['setting_id']);

        // Group slots by teacher
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
                    'time' => $s->period?->getDurationLabel(),
                    'subject' => $s->subject?->subject,
                    'room' => $s->room,
                ])->toArray(),
                'type' => $validated['type'],
                'generated' => now()->format('d M Y H:i'),
            ];

            try {
                Mail::to($teacher->email)->send(new TimetableNotificationMail($notifData));

                // Log notification
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
    // SCHEDULE NOTIFICATION FOR SPECIFIC TEACHER
    // =========================================================================

    private function scheduleNotification(int $teacherId, int $slotId, string $type): void
    {
        $teacher = User::find($teacherId);
        if (!$teacher || !$teacher->email) return;

        $slot = TimetableSlot::with(['period', 'subject'])->find($slotId);

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

    // =========================================================================
    // CONFLICT CHECKER
    // =========================================================================

    public function checkConflicts(int $settingId): JsonResponse
    {
        $slots = TimetableSlot::where('setting_id', $settingId)
            ->whereNotNull('teacher_id')
            ->with(['period', 'subject', 'setting.schoolclass', 'teacher'])
            ->get();

        // Find teacher double-booked at same time on same day
        $conflicts = [];
        $teacherSlotMap = [];

        foreach ($slots as $slot) {
            $key = $slot->teacher_id . '_' . $slot->day . '_' . $slot->period_id;
            if (isset($teacherSlotMap[$key])) {
                $conflicts[] = [
                    'type' => 'teacher_conflict',
                    'day' => $slot->day,
                    'period' => $slot->period?->name,
                    'period_time' => $slot->period?->getDurationLabel(),
                    'teacher' => $slot->teacher?->name,
                    'teacher_picture' => $slot->teacher && $slot->teacher->staffPicture
                        ? asset('storage/staff_avatars/' . $slot->teacher->staffPicture->picture)
                        : null,
                    'subject_a' => $teacherSlotMap[$key]->subject?->subject,
                    'subject_b' => $slot->subject?->subject,
                    'class_a' => $teacherSlotMap[$key]->setting?->schoolclass?->schoolclass,
                    'class_b' => $slot->setting?->schoolclass?->schoolclass,
                ];
            } else {
                $teacherSlotMap[$key] = $slot;
            }
        }

        // Cross-setting check
        $currentSetting = TimetableSetting::find($settingId);
        if ($currentSetting) {
            $allSettingIds = TimetableSetting::where('session_id', $currentSetting->session_id)
                ->where('id', '!=', $settingId)
                ->pluck('id')
                ->toArray();

            if (count($allSettingIds) > 0) {
                $allSlots = TimetableSlot::whereIn('setting_id', $allSettingIds)
                    ->whereNotNull('teacher_id')
                    ->with(['period', 'subject', 'setting.schoolclass', 'teacher'])
                    ->get();

                $allTeacherSlotMap = [];
                foreach ($allSlots as $s) {
                    $k = $s->teacher_id . '_' . $s->day . '_' . $s->period_id;
                    if (isset($allTeacherSlotMap[$k]) && $allTeacherSlotMap[$k]->setting_id !== $s->setting_id) {
                        $conflicts[] = [
                            'type' => 'cross_class_conflict',
                            'day' => $s->day,
                            'period' => $s->period?->name,
                            'period_time' => $s->period?->getDurationLabel(),
                            'teacher' => $s->teacher?->name,
                            'teacher_picture' => $s->teacher && $s->teacher->staffPicture
                                ? asset('storage/staff_avatars/' . $s->teacher->staffPicture->picture)
                                : null,
                            'class_a' => $allTeacherSlotMap[$k]->setting?->schoolclass?->schoolclass,
                            'class_b' => $s->setting?->schoolclass?->schoolclass,
                        ];
                    } else {
                        $allTeacherSlotMap[$k] = $s;
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'conflicts' => $conflicts,
            'conflict_count' => count($conflicts),
        ]);
    }

    // =========================================================================
    // EXPORT TIMETABLE (CSV/PDF)
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

        if ($format === 'csv') {
            $filename = "timetable_{$setting->schoolclass->schoolclass}_{$setting->session->session}.csv";
            $handle = fopen('php://temp', 'w+');

            // Header row
            $header = ['Period', 'Time'];
            foreach ($days as $day) {
                $header[] = $day;
            }
            fputcsv($handle, $header);

            // Data rows
            foreach ($periods as $period) {
                $row = [
                    $period->name,
                    $period->start_time . '-' . $period->end_time,
                ];
                foreach ($days as $day) {
                    $slot = $grid[$period->id][$day] ?? ['subject' => '—', 'teacher' => '—'];
                    $row[] = $slot['subject'] . "\n" . $slot['teacher'];
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

        return response()->json(['success' => false, 'message' => 'PDF export coming soon'], 501);
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

            // Clone periods
            foreach ($oldSetting->periods as $period) {
                $newPeriod = $period->replicate();
                $newPeriod->setting_id = $newSetting->id;
                $newPeriod->save();
            }

            // Clone constraints
            foreach ($oldSetting->constraints as $constraint) {
                $newConstraint = $constraint->replicate();
                $newConstraint->setting_id = $newSetting->id;
                $newConstraint->save();
            }

            DB::commit();
            return response()->json(['success' => true, 'setting_id' => $newSetting->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
                $slotTime = Carbon::createFromFormat('H:i:s', $slot->period->start_time);
                return $slotTime->greaterThan($now) || $slotTime->equalTo($now);
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

    private function logTimetableChange(int $userId, string $action, string $modelType, ?int $modelId, $oldValues = null, $newValues = null): void
    {
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
    }


    // Add to TimetableController.php

// Request substitute
public function requestSubstitute(Request $request): JsonResponse
{
    $validated = $request->validate([
        'slot_id' => 'required|exists:timetable_slots,id',
        'substitute_teacher_id' => 'required|exists:users,id',
        'reason' => 'required|string|max:500',
        'assignment_date' => 'required|date|after_or_equal:today',
    ]);

    $slot = TimetableSlot::findOrFail($validated['slot_id']);

    // Check if teacher owns this slot
    if ($slot->teacher_id != Auth::id()) {
        return response()->json(['success' => false, 'message' => 'You can only request substitutes for your own classes'], 403);
    }

    // Check if substitute is available
    $isAvailable = $this->checkTeacherAvailability(
        $validated['substitute_teacher_id'],
        $slot->day,
        $slot->period->start_time,
        $slot->period->end_time,
        $validated['assignment_date']
    );

    if (!$isAvailable) {
        return response()->json(['success' => false, 'message' => 'Substitute teacher is not available at this time'], 422);
    }

    $substitute = SubstituteAssignment::create([
        'original_teacher_id' => Auth::id(),
        'substitute_teacher_id' => $validated['substitute_teacher_id'],
        'slot_id' => $slot->id,
        'assignment_date' => $validated['assignment_date'],
        'reason' => $validated['reason'],
        'status' => 'pending',
    ]);

    // Send notification to admin for approval
    $this->sendSubstituteRequestNotification($substitute);

    return response()->json(['success' => true, 'substitute' => $substitute]);
}

// Approve substitute
public function approveSubstitute(Request $request, int $substituteId): JsonResponse
{
    $substitute = SubstituteAssignment::findOrFail($substituteId);

    // Check admin permission
    if (!Auth::user()->hasRole('admin')) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $substitute->update([
        'status' => 'approved',
        'approved_by' => Auth::id(),
        'approved_at' => now(),
    ]);

    // Update the actual slot temporarily
    $slot = $substitute->slot;

    // Store original teacher in metadata
    $slot->update([
        'teacher_id' => $substitute->substitute_teacher_id,
        'notes' => ($slot->notes ? $slot->notes . "\n" : '') .
                   "[SUBSTITUTE] Original: {$substitute->originalTeacher->name}, Date: {$substitute->assignment_date}",
    ]);

    // Notify both teachers
    $this->sendSubstituteApprovalNotification($substitute);

    return response()->json(['success' => true]);
}

// Get substitute requests for admin
public function getSubstituteRequests(Request $request): JsonResponse
{
    $query = SubstituteAssignment::with(['originalTeacher', 'substituteTeacher', 'slot.period', 'slot.subject'])
        ->when($request->status, fn($q) => $q->where('status', $request->status))
        ->when($request->date, fn($q) => $q->whereDate('assignment_date', $request->date))
        ->orderBy('created_at', 'desc');

    $requests = $query->paginate($request->per_page ?? 20);

    return response()->json(['success' => true, 'requests' => $requests]);
}


// Add to TimetableController.php

public function workloadDashboard(Request $request): JsonResponse
{
    $sessionId = $request->session_id ?? Schoolsession::where('status', 'Current')->value('id');
    $termId = $request->term_id ?? Schoolterm::where('status', true)->value('id');

    // Get all teachers with their workload
    $teachers = User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))
        ->with(['workloadSetting'])
        ->get();

    $workloadData = [];

    foreach ($teachers as $teacher) {
        // Count periods assigned
        $periodsCount = TimetableSlot::where('teacher_id', $teacher->id)
            ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId)->where('term_id', $termId))
            ->count();

        // Get classes taught
        $classes = TimetableSlot::where('teacher_id', $teacher->id)
            ->whereHas('setting', fn($q) => $q->where('session_id', $sessionId))
            ->with('setting.schoolclass')
            ->get()
            ->pluck('setting.schoolclass.schoolclass')
            ->unique()
            ->values();

        // Get subjects taught
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
            'max_periods_allowed' => $teacher->workloadSetting->max_periods_per_week ?? 30,
            'utilization_percentage' => $teacher->workloadSetting ?
                round(($periodsCount / $teacher->workloadSetting->max_periods_per_week) * 100) : 0,
            'classes_taught' => $classes,
            'subjects_taught' => $subjects,
            'status' => $this->getWorkloadStatus($periodsCount, $teacher->workloadSetting),
        ];
    }

    // Calculate overall statistics
    $stats = [
        'total_teachers' => $teachers->count(),
        'total_periods_assigned' => collect($workloadData)->sum('periods_assigned'),
        'average_utilization' => collect($workloadData)->avg('utilization_percentage'),
        'overloaded_teachers' => collect($workloadData)->where('status', 'overloaded')->count(),
        'underutilized_teachers' => collect($workloadData)->where('status', 'underutilized')->count(),
    ];

    return response()->json([
        'success' => true,
        'workload' => $workloadData,
        'statistics' => $stats,
    ]);
}

private function getWorkloadStatus($periodsCount, $setting)
{
    if (!$setting) return 'not_configured';

    $max = $setting->max_periods_per_week;
    if ($periodsCount > $max) return 'overloaded';
    if ($periodsCount < $max * 0.6) return 'underutilized';
    return 'optimal';
}


// Add to TimetableController.php

public function generateAnalytics(Request $request): JsonResponse
{
    $type = $request->type;
    $sessionId = $request->session_id;
    $termId = $request->term_id;

    switch ($type) {
        case 'teacher_utilization':
            $data = $this->getTeacherUtilizationReport($sessionId, $termId);
            break;
        case 'room_utilization':
            $data = $this->getRoomUtilizationReport($sessionId, $termId);
            break;
        case 'class_distribution':
            $data = $this->getClassDistributionReport($sessionId, $termId);
            break;
        case 'conflict_analysis':
            $data = $this->getConflictAnalysisReport($sessionId, $termId);
            break;
        default:
            return response()->json(['success' => false, 'message' => 'Invalid report type'], 422);
    }

    // Save report
    $report = TimetableReport::create([
        'report_name' => ucfirst(str_replace('_', ' ', $type)) . ' Report - ' . now()->format('Y-m-d H:i'),
        'report_type' => $type,
        'session_id' => $sessionId,
        'term_id' => $termId,
        'filters' => $request->all(),
        'data' => $data,
        'generated_by' => Auth::id(),
    ]);

    return response()->json([
        'success' => true,
        'report' => $report,
        'data' => $data,
    ]);
}

private function getTeacherUtilizationReport($sessionId, $termId): array
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
            'total_periods' => $slots->count(),
            'daily_distribution' => $dailyDistribution,
            'subjects_taught' => $slots->pluck('subject.subject')->unique()->values(),
            'classes_taught' => $slots->pluck('setting.schoolclass.schoolclass')->unique()->values(),
            'peak_day' => array_search(max($dailyDistribution), $dailyDistribution),
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
                'percentage' => round(($dayBookings->count() / 8) * 100), // Assuming 8 periods max
            ];
        }

        $report[] = [
            'room_name' => $room->room_name,
            'room_code' => $room->room_code,
            'capacity' => $room->capacity,
            'total_bookings' => $bookings->count(),
            'utilization_by_day' => $utilizationByDay,
            'average_utilization' => collect($utilizationByDay)->avg('percentage'),
        ];
    }

    return $report;
}
}
