<?php

// app/Models/StaffAttendance.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    protected $table = 'staff_attendance';
    protected $fillable = [
        'staff_id', 'attendance_date', 'time_in', 'time_out',
        'status', 'source', 'marked_by', 'notes',
    ];
    protected $casts = ['attendance_date' => 'date'];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
