<?php
// app/Models/TimetableSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableSetting extends Model
{
    protected $table = 'timetable_settings';

    protected $fillable = [
        'schoolclass_id', 'session_id', 'term_id',
        'school_day_start', 'school_day_end',
        'period_duration_minutes', 'short_break_duration_minutes', 'long_break_duration_minutes',
        'is_active', 'active_days',
    ];

    protected $casts = [
        'active_days' => 'array',
        'is_active' => 'boolean',
    ];

    public function schoolclass(): BelongsTo
    {
        return $this->belongsTo(Schoolclass::class, 'schoolclass_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Schoolsession::class, 'session_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Schoolterm::class, 'term_id');
    }

    public function periods(): HasMany
    {
        return $this->hasMany(TimetablePeriod::class, 'setting_id')->orderBy('order');
    }

    public function constraints(): HasMany
    {
        return $this->hasMany(TimetableConstraint::class, 'setting_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(TimetableSlot::class, 'setting_id');
    }
}
