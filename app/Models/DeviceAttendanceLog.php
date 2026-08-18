<?php

// app/Models/DeviceAttendanceLog.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DeviceAttendanceLog extends Model
{
    protected $table = 'device_attendance_logs';
    protected $fillable = [
        'device_serial', 'device_pin', 'punch_time',
        'verify_mode', 'status_code', 'processing_status', 'process_note',
    ];
    protected $casts = ['punch_time' => 'datetime'];
}
