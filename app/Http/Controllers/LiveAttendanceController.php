<?php

// app/Http/Controllers/LiveAttendanceController.php
namespace App\Http\Controllers;

use App\Http\Controllers\DeviceUserMappingController;
use App\Models\DeviceAttendanceLog;
use App\Models\DeviceUserMapping;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Http\Request;

class LiveAttendanceController extends Controller
{
    public function feed(Request $request)
    {
        $sinceId = (int) $request->input('since_id', 0);

        $logs = DeviceAttendanceLog::where('id', '>', $sinceId)
            ->where('processing_status', 'processed')
            ->orderBy('id')
            ->limit(100)
            ->get();

        if ($logs->isEmpty()) {
            return response()->json(['logs' => [], 'last_id' => $sinceId]);
        }

        $pins = $logs->pluck('device_pin')->unique();
        $mappings = DeviceUserMapping::whereIn('device_pin', $pins)->get()->keyBy('device_pin');

        $studentIds = $mappings->where('person_type', 'student')->pluck('person_id');
        $staffIds   = $mappings->where('person_type', 'staff')->pluck('person_id');
        $students   = Student::whereIn('id', $studentIds)->get()->keyBy('id');
        $staff      = Staff::with('user')->whereIn('id', $staffIds)->get()->keyBy('id');

        $out = $logs->map(function ($log) use ($mappings, $students, $staff) {
            $mapping = $mappings->get($log->device_pin);
            $name = 'Unknown';
            $type = 'unknown';
            if ($mapping) {
                $type = $mapping->person_type;
                if ($type === 'student' && $students->has($mapping->person_id)) {
                    $s = $students->get($mapping->person_id);
                    $name = "{$s->lastname} {$s->firstname}";
                } elseif ($type === 'staff' && $staff->has($mapping->person_id)) {
                    $name = $staff->get($mapping->person_id)->full_name;
                }
            }
            return [
                'id'         => $log->id,
                'name'       => $name,
                'type'       => $type,
                'punch_time' => $log->punch_time->format('h:i A'),
                'date'       => $log->punch_time->format('Y-m-d'),
            ];
        });

        return response()->json(['logs' => $out, 'last_id' => $logs->last()->id]);
    }
}