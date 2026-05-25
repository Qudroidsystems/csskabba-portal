<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subjectclass extends Model
{
    use HasFactory;

    protected $table = "subjectclass";

    protected $fillable = [
        'schoolclassid',
        'subjectid',
        'subjectteacherid',
        'termid',
        'sessionid',
        'status',
        'teacher_editing_enabled',      // New field for lock system
        'teacher_editing_disabled_at',  // When teacher editing was disabled
        'teacher_editing_disabled_by',  // Who disabled teacher editing
    ];

    protected $casts = [
        'teacher_editing_enabled' => 'boolean',
        'teacher_editing_disabled_at' => 'datetime',
    ];

    /**
     * Get the subject teacher associated with the subject class.
     */
    public function subjectTeacher()
    {
        return $this->belongsTo(SubjectTeacher::class, 'subjectteacherid', 'id');
    }

    /**
     * Get the school class associated with the subject class.
     */
    public function schoolClass()
    {
        return $this->belongsTo(Schoolclass::class, 'schoolclassid', 'id');
    }

    /**
     * Get the subject associated with the subject class.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subjectid', 'id');
    }

    public function session()
    {
        return $this->belongsTo(Schoolsession::class, 'sessionid', 'id');
    }

    public function term()
    {
        return $this->belongsTo(Schoolterm::class, 'termid', 'id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staffid');
    }

    /**
     * Get the user who disabled teacher editing
     */
    public function teacherEditingDisabledBy()
    {
        return $this->belongsTo(User::class, 'teacher_editing_disabled_by');
    }

    /**
     * Get all broadsheets for this subject class
     */
    public function broadsheets()
    {
        return $this->hasMany(Broadsheets::class, 'subjectclass_id', 'id');
    }

    /**
     * Get all scoresheet locks for this subject class
     */
    public function scoresheetLocks()
    {
        return $this->hasMany(ScoresheetLock::class, 'subjectclass_id', 'id');
    }

    /**
     * Check if teacher editing is currently enabled
     */
    public function isTeacherEditingEnabled(): bool
    {
        return (bool) $this->teacher_editing_enabled;
    }

    /**
     * Disable teacher editing for this subject class
     */
    public function disableTeacherEditing($userId = null, $reason = null)
    {
        $this->teacher_editing_enabled = false;
        $this->teacher_editing_disabled_at = now();
        $this->teacher_editing_disabled_by = $userId ?? auth()->id();
        $this->save();

        // Also lock all related broadsheets
        $this->broadsheets()->update([
            'is_locked' => true,
            'locked_by' => $userId ?? auth()->id(),
            'locked_at' => now(),
            'lock_reason' => $reason ?: 'Teacher editing disabled by admin',
        ]);

        return $this;
    }

    /**
     * Enable teacher editing for this subject class
     */
    public function enableTeacherEditing()
    {
        $this->teacher_editing_enabled = true;
        $this->teacher_editing_disabled_at = null;
        $this->teacher_editing_disabled_by = null;
        $this->save();

        // Unlock all related broadsheets that don't have individual locks
        $this->broadsheets()
            ->where('is_locked', true)
            ->update([
                'is_locked' => false,
                'locked_by' => null,
                'locked_at' => null,
                'lock_reason' => null,
            ]);

        return $this;
    }

    /**
     * Get active global lock for this subject class in a specific term/session
     */
    public function getActiveGlobalLock($termId, $sessionId)
    {
        return $this->scoresheetLocks()
            ->where('term_id', $termId)
            ->where('session_id', $sessionId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Check if there's an active global lock for this subject class in a specific term/session
     */
    public function hasActiveGlobalLock($termId, $sessionId): bool
    {
        return $this->scoresheetLocks()
            ->where('term_id', $termId)
            ->where('session_id', $sessionId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get all locked broadsheets for this subject class
     */
    public function getLockedBroadsheets($termId = null, $sessionId = null)
    {
        $query = $this->broadsheets()->where('is_locked', true);

        if ($termId) {
            $query->where('term_id', $termId);
        }
        if ($sessionId) {
            $query->whereHas('broadsheetRecord', function($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            });
        }

        return $query->get();
    }

    /**
     * Get lock statistics for this subject class
     */
    public function getLockStats($termId = null, $sessionId = null)
    {
        $query = $this->broadsheets();

        if ($termId) {
            $query->where('term_id', $termId);
        }
        if ($sessionId) {
            $query->whereHas('broadsheetRecord', function($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            });
        }

        $total = $query->count();
        $locked = $query->where('is_locked', true)->count();

        return [
            'total' => $total,
            'locked' => $locked,
            'unlocked' => $total - $locked,
            'percentage_locked' => $total > 0 ? round(($locked / $total) * 100) : 0,
        ];
    }

    /**
     * Get the class name with arm
     */
    public function getFullClassNameAttribute()
    {
        $arm = $this->schoolClass?->arm?->arm ?? '';
        return trim($this->schoolClass?->schoolclass . ' ' . $arm);
    }

    /**
     * Get the subject name with code
     */
    public function getSubjectDisplayAttribute()
    {
        return $this->subject?->subject . ' (' . $this->subject?->subject_code . ')';
    }
}
