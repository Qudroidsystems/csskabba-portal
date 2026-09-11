<?php

namespace App\Services;

use App\Http\Controllers\AttendanceSettingController;
use App\Models\AttendanceTermSetting;
use App\Models\DeviceAttendanceLog;
use App\Models\DeviceOutageDate;
use App\Models\DeviceUserMapping;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\StaffAttendance;
use App\Models\StaffAttendanceTimeSetting;
use App\Models\Studentclass;
use App\Models\StudentAttendance;
use Illuminate\Support\Carbon;

/**
 * Turns a single raw device punch (DeviceAttendanceLog) into either a
 * StudentAttendance row or a StaffAttendance row, based on the PIN mapping.
 *
 * Student lateness / afternoon cutoffs now come from AttendanceTermSetting
 * (admin-editable). Staff lateness comes from StaffAttendanceTimeSetting.
 */
class DeviceAttendanceProcessor
{
    // Fallbacks only — used if no term setting exists for the current term.
    const DEFAULT_MORNING_CUTOFF  = '08:00:00';
    const DEFAULT_AFTERNOON_START = '12:00:00';

    public function process(DeviceAttendanceLog $log): void
    {
        // Device outage days are excluded from processing entirely so nobody
        // gets a spurious attendance row (or spurious absence) for that date.
        $punchDate = Carbon::parse($log->punch_time)->toDateString();
        if (DeviceOutageDate::where('outage_date', $punchDate)->exists()) {
            $log->update([
                'processing_status' => 'error',
                'process_note'      => 'Punch date flagged as device outage',
            ]);
            return;
        }

        $mapping = DeviceUserMapping::where('device_serial', $log->device_serial)
            ->where('device_pin', $log->device_pin)
            ->where('active', true)
            ->first();

        if (!$mapping) {
            $log->update([
                'processing_status' => 'unmapped',
                'process_note'      => 'No mapping for this PIN',
            ]);
            return;
        }

        try {
            if ($mapping->person_type === 'student') {
                $this->processStudent($mapping->person_id, $log);
            } else {
                $this->processStaff($mapping->person_id, $log);
            }
            $log->update(['processing_status' => 'processed', 'process_note' => null]);
        } catch (\Exception $e) {
            $log->update([
                'processing_status' => 'error',
                'process_note'      => $e->getMessage(),
            ]);
        }
    }

    private function processStudent(int $studentId, DeviceAttendanceLog $log): void
    {
        $punch = Carbon::parse($log->punch_time);
        $date  = $punch->toDateString();
        $time  = $punch->format('H:i:s');

        $session = Schoolsession::where('status', 'Current')->first();
        $term    = Schoolterm::where('status', 'Current')->first();
        if (!$session || !$term) {
            throw new \RuntimeException('No current session/term configured.');
        }

        $studentClass = Studentclass::where('studentId', $studentId)
            ->where('sessionid', $session->id)
            ->first();
        if (!$studentClass) {
            throw new \RuntimeException('Student has no class assignment for the current session.');
        }

        $setting = AttendanceTermSetting::where('term_id', $term->id)
            ->where('session_id', $session->id)
            ->first();
        if (!$setting) {
            throw new \RuntimeException('Attendance not configured for the current term.');
        }

        // ── Times now come from the admin-configurable term setting ──
        // Fallbacks guard against legacy rows saved before the migration.
        $dayStart       = $punch->copy()->startOfDay();
        $morningCutoff  = $setting->resumption_time
            ? $setting->morningCutoffFor($dayStart)
            : $dayStart->copy()->setTimeFromTimeString(self::DEFAULT_MORNING_CUTOFF);
        $afternoonStart = $setting->morning_end_time
            ? $setting->afternoonStartFor($dayStart)
            : $dayStart->copy()->setTimeFromTimeString(self::DEFAULT_AFTERNOON_START);

        $isAfternoon = $setting->track_afternoon && $punch->gte($afternoonStart);
        $period      = $isAfternoon ? 'afternoon' : 'morning';

        // Status only evaluated for morning punches; afternoon punches are
        // always 'present' (no separate afternoon lateness rule yet).
        $status = (!$isAfternoon && $punch->gt($morningCutoff)) ? 'late' : 'present';

        $keys = [
            'student_id'      => $studentId,
            'schoolclass_id'  => $studentClass->schoolclassid,
            'term_id'         => $term->id,
            'session_id'      => $session->id,
            'attendance_date' => $date,
            'period'          => $period,
        ];

        // First punch of the period sets status + time_in and is never
        // downgraded by a later duplicate punch; subsequent punches update time_out.
        $existing = StudentAttendance::where($keys)->first();

        $attendance = StudentAttendance::updateOrCreate($keys, [
            'status'   => $existing->status ?? $status,
            'time_in'  => $existing->time_in ?? $time,
            'time_out' => $time,
            'source'   => 'device',
        ]);

        // Only rebuild when this punch could actually have changed the summary:
        // a brand-new row, or a status change. Every later "time_out only"
        // punch skips this entirely — keeps a morning rush of repeat punches cheap.
        if (!$existing || $existing->status !== $attendance->status) {
            AttendanceSettingController::rebuildSummary(
                $studentId,
                $studentClass->schoolclassid,
                $term->id,
                $session->id
            );
        }
    }

    private function processStaff(int $staffId, DeviceAttendanceLog $log): void
    {
        $punch = Carbon::parse($log->punch_time);
        $date  = $punch->toDateString();
        $time  = $punch->format('H:i:s');

        $existing = StaffAttendance::where('staff_id', $staffId)
            ->where('attendance_date', $date)
            ->first();

        // Status is decided once, on the FIRST punch of the day — a later
        // time_out-only punch never downgrades an already-recorded status.
        if ($existing) {
            $status = $existing->status;
        } else {
            $settings = StaffAttendanceTimeSetting::current();
            $cutoff   = $settings->cutoffFor($punch->copy()->startOfDay());
            $status   = $punch->gt($cutoff) ? 'late' : 'present';
        }

        StaffAttendance::updateOrCreate(
            ['staff_id' => $staffId, 'attendance_date' => $date],
            [
                'time_in'  => $existing->time_in ?? $time,
                'time_out' => $time,
                'status'   => $status,
                'source'   => 'device',
            ]
        );
    }
}