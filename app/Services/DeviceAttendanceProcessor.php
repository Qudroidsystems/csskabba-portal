<?php
// app/Services/PromotionEvaluator.php

namespace App\Services;

use App\Models\AttendanceTermSetting;
use App\Models\ClassTeacher;
use App\Models\DeviceAttendanceLog;
use App\Models\DeviceUserMapping;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\StaffAttendance;
use App\Models\Studentclass;
use App\Models\StudentAttendance;
use App\Http\Controllers\AttendanceSettingController;
use Illuminate\Support\Carbon;

class DeviceAttendanceProcessor
{
    // Configure these or pull from a settings table later
    const MORNING_CUTOFF   = '08:00:00'; // after this = 'late' for students
    const AFTERNOON_START  = '12:00:00'; // punches after this go to afternoon period
    const STAFF_LATE_AFTER = '08:30:00';

    public function process(DeviceAttendanceLog $log): void
    {
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
            $log->update(['processing_status' => 'processed']);
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
        $term    = Schoolterm::where('status', 'Current')->first(); // adjust to your actual "current term" logic

        $studentClass = Studentclass::where('studentId', $studentId)
            ->where('sessionid', $session->id)
            ->first();
        if (!$studentClass) return;

        $setting = AttendanceTermSetting::where('term_id', $term->id)
            ->where('session_id', $session->id)
            ->first();
        if (!$setting) return;

        $period = ($time >= self::AFTERNOON_START && $setting->track_afternoon) ? 'afternoon' : 'morning';
        $status = ($period === 'morning' && $time > self::MORNING_CUTOFF) ? 'late' : 'present';

        $existing = StudentAttendance::where([
            'student_id'      => $studentId,
            'schoolclass_id'  => $studentClass->schoolclassid,
            'term_id'         => $term->id,
            'session_id'      => $session->id,
            'attendance_date' => $date,
            'period'          => $period,
        ])->first();

        // First punch of the period = time_in; don't downgrade an existing 'present' to 'late' from a later duplicate punch
        StudentAttendance::updateOrCreate(
            [
                'student_id'      => $studentId,
                'schoolclass_id'  => $studentClass->schoolclassid,
                'term_id'         => $term->id,
                'session_id'      => $session->id,
                'attendance_date' => $date,
                'period'          => $period,
            ],
            [
                'status'   => $existing->status ?? $status,
                'time_in'  => $existing->time_in ?? $time,
                'time_out' => $time, // last punch of the day becomes time_out
                'source'   => 'device',
            ]
        );

        AttendanceSettingController::rebuildSummary(
            $studentId, $studentClass->schoolclassid, $term->id, $session->id
        );
    }

    private function processStaff(int $staffId, DeviceAttendanceLog $log): void
    {
        $punch = Carbon::parse($log->punch_time);
        $date  = $punch->toDateString();
        $time  = $punch->format('H:i:s');

        $existing = StaffAttendance::where('staff_id', $staffId)
            ->where('attendance_date', $date)
            ->first();

        $status = $existing?->status
            ?? ($time > self::STAFF_LATE_AFTER ? 'late' : 'present');

        StaffAttendance::updateOrCreate(
            ['staff_id' => $staffId, 'attendance_date' => $date],
            [
                'time_in'  => $existing->time_in ?? $time, // keep the first punch as time_in
                'time_out' => $time,                        // update time_out on every subsequent punch
                'status'   => $status,
                'source'   => 'device',
            ]
        );
    }
}
