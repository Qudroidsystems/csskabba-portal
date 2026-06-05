<?php
// app/Models/Schoolterm.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schoolterm extends Model
{
    use HasFactory;

    protected $table = "schoolterm";

    protected $fillable = [
        'term',
        'status',
        'is_promotional',
    ];

    protected $casts = [
        'status'         => 'boolean',
        'is_promotional' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    public function scopePromotional($query)
    {
        return $query->where('is_promotional', true);
    }
}
