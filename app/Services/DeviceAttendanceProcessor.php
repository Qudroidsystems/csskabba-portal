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
 * Called synchronously from Api\DeviceAttendanceController::store() and from
 * DeviceUserMappingController::reprocessPendingLogs() at current volumes
 * (~1,200 mapped users). If punch volume grows significantly, dispatch
 * process() from a queued Job instead of calling it inline.
 */
class DeviceAttendanceProcessor
{
    // Adjust these to match the school's actual bell schedule, or move to a
    // settings table later if different periods need different cutoffs.
    const MORNING_CUTOFF  = '08:00:00'; // punch after this = 'late' for students (morning period)
    const AFTERNOON_START = '12:00:00'; // punches after this go to the afternoon period

    // Staff lateness now comes from StaffAttendanceTimeSetting::current()
    // (admin-configurable, see StaffAttendanceTimeSettingController) instead
    // of a hardcoded cutoff.

    public function process(DeviceAttendanceLog $log): void
    {
        // Device outage days are excluded from processing entirely so nobody
        // gets a spurious attendance row (or spurious absence) recorded for that date.
        $punchDate = Carbon::parse($log->punch_time)->toDateString();
        if (DeviceOutageDate::where('outage_date', $punchDate)->exists()) {
            $log->update(['processing_status' => 'error', 'process_note' => 'Punch date flagged as device outage']);
            return;
        }

        $mapping = DeviceUserMapping::where('device_serial', $log->device_serial)
            ->where('device_pin', $log->device_pin)
            ->where('active', true)
            ->first();

        if (!$mapping) {
            $log->update(['processing_status' => 'unmapped', 'process_note' => 'No mapping for this PIN']);
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
            $log->update(['processing_status' => 'error', 'process_note' => $e->getMessage()]);
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

        $period = ($time >= self::AFTERNOON_START && $setting->track_afternoon) ? 'afternoon' : 'morning';
        $status = ($period === 'morning' && $time > self::MORNING_CUTOFF) ? 'late' : 'present';

        $keys = [
            'student_id'      => $studentId,
            'schoolclass_id'  => $studentClass->schoolclassid,
            'term_id'         => $term->id,
            'session_id'      => $session->id,
            'attendance_date' => $date,
            'period'          => $period,
        ];

        // First punch of the period sets status + time_in and is never downgraded
        // by a later duplicate punch; every subsequent punch that day updates time_out.
        $existing = StudentAttendance::where($keys)->first();

        $attendance = StudentAttendance::updateOrCreate($keys, [
            'status'   => $existing->status ?? $status,
            'time_in'  => $existing->time_in ?? $time,
            'time_out' => $time,
            'source'   => 'device',
        ]);

        // Only rebuild when this punch could actually have changed the summary:
        // a brand-new row, or a status change. Every later "time_out only"
        // punch for an already-recorded student/period skips this entirely —
        // this is what keeps a morning rush of repeat punches cheap.
        if (!$existing || $existing->status !== $attendance->status) {
            AttendanceSettingController::rebuildSummary(
                $studentId, $studentClass->schoolclassid, $term->id, $session->id
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

        // Status is decided once, on the FIRST punch of the day, exactly like
        // the student path above — a later time_out-only punch never
        // downgrades an already-recorded 'present' to 'late' or vice versa.
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