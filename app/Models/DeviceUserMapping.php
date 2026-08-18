<?php

// app/Models/DeviceUserMapping.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DeviceUserMapping extends Model
{
    protected $fillable = ['device_serial', 'device_pin', 'person_type', 'person_id', 'active'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'person_id')->when(
            $this->person_type === 'student', fn($q) => $q
        );
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'person_id');
    }

    public function person()
    {
        return $this->person_type === 'student'
            ? $this->belongsTo(Student::class, 'person_id')->getResults()
            : $this->belongsTo(Staff::class, 'person_id')->getResults();
    }
}
