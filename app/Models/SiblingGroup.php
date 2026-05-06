<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiblingGroup extends Model
{
    use HasFactory;

    protected $table = 'sibling_groups';

    protected $fillable = [
        'group_no',
        'family_name',
        'parent_phone',
        'parent_email',
        'address',
        'total_children',
        'discount_type',
        'discount_value',
        'primary_contact_id'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'total_children' => 'integer',
    ];

    /**
     * Relationship with students - using the correct pivot table name
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'sibling_group_students', 'sibling_group_id', 'student_id')
                    ->withTimestamps();
    }

    /**
     * Relationship with primary contact user
     */
    public function primaryContact()
    {
        return $this->belongsTo(User::class, 'primary_contact_id');
    }

    /**
     * Relationship with discount assignments
     */
    public function discountAssignments()
    {
        return $this->hasMany(DiscountAssignment::class, 'sibling_group_id');
    }

    /**
     * Get all siblings of a student
     */
    public static function getSiblings($studentId)
    {
        $group = self::whereHas('students', function($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })->first();

        if (!$group) {
            return collect([]);
        }

        return $group->students()->where('student_id', '!=', $studentId)->get();
    }

    /**
     * Check if student has siblings
     */
    public static function hasSiblings($studentId)
    {
        return self::getSiblings($studentId)->count() > 0;
    }

    /**
     * Calculate sibling discount for a specific student in the group
     */
    public function calculateSiblingDiscount($studentId)
    {
        $siblings = $this->students;
        $studentOrder = 0;

        // Find the order of this student in siblings list (by birth order or ID order)
        foreach ($siblings as $index => $sibling) {
            if ($sibling->id == $studentId) {
                $studentOrder = $index + 1;
                break;
            }
        }

        if ($this->discount_type === 'percentage') {
            $discountValue = $this->discount_value;
            // Additional discount for later children (5% per additional child after 1st, max 50%)
            if ($studentOrder > 1) {
                $discountValue += ($studentOrder - 1) * 5;
            }
            return min($discountValue, 50); // Cap at 50%
        }

        // Fixed amount per child
        return $this->discount_value;
    }

    /**
     * Get birth order of a student in the family
     */
    public function getBirthOrder($studentId)
    {
        $order = 1;
        foreach ($this->students as $index => $student) {
            if ($student->id == $studentId) {
                $order = $index + 1;
                break;
            }
        }
        return $order;
    }
}
