<?php
// app/Models/Room.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;

    // Add this - specify which fields can be mass assigned
    protected $fillable = [
        'room_code',
        'room_name',
        'type',
        'capacity',
        'facilities',
        'building',
        'floor',
        'is_active',
        'notes'
    ];

    // Add casts for proper data type handling
    protected $casts = [
        'facilities' => 'array',  // Automatically handle JSON conversion
        'is_active' => 'boolean',
        'capacity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Optional: Add any relationships if needed
    public function timetableSlots()
    {
        return $this->hasMany(TimetableSlot::class);
    }

    public function bookings()
    {
        return $this->hasMany(RoomBooking::class);
    }

    // Optional: Accessor to always return facilities as array
    public function getFacilitiesAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        return json_decode($value, true) ?? [];
    }

    // Optional: Mutator to always store facilities as JSON
    public function setFacilitiesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['facilities'] = json_encode($value);
        } else {
            $this->attributes['facilities'] = $value;
        }
    }
}
