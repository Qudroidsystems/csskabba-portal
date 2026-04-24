<?php
// app/Services/Scholarship/ScholarshipService.php

namespace App\Services\Scholarship;

use App\Models\Scholarship;
use App\Models\ScholarshipAssignment;
use App\Models\ScholarshipApplication;
use App\Models\Student;
use App\Models\SchoolBillModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ScholarshipService
{
    /**
     * Apply scholarship to a student's bill
     */
    public function applyScholarshipToBill($studentId, $billId, $termId, $sessionId, $classId = null)
    {
        $bill = SchoolBillModel::findOrFail($billId);

        // Check if bill is scholarship eligible
        if (!$bill->is_scholarship_eligible) {
            return [
                'applied' => false,
                'reason' => 'Bill is not scholarship eligible',
                'deduction' => 0
            ];
        }

        // Get active scholarship assignments for student
        $scholarshipAssignments = ScholarshipAssignment::where('student_id', $studentId)
            ->where('status', 'active')
            ->where('effective_from', '<=', now())
            ->where(function($q) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', now());
            })
            ->with('scholarship')
            ->get();

        if ($scholarshipAssignments->isEmpty()) {
            return [
                'applied' => false,
                'reason' => 'No active scholarship found',
                'deduction' => 0
            ];
        }

        $totalDeduction = 0;
        $appliedScholarships = [];

        foreach ($scholarshipAssignments as $assignment) {
            $scholarship = $assignment->scholarship;

            // Check if scholarship excludes this bill category
            if ($scholarship->excluded_bill_categories) {
                $excluded = json_decode($scholarship->excluded_bill_categories, true) ?? [];
                if (in_array($bill->category, $excluded)) {
                    continue;
                }
            }

            // Calculate deduction
            $remainingBillAmount = $bill->bill_amount - $totalDeduction;
            if ($remainingBillAmount <= 0) break;

            $deduction = $assignment->value_type === 'percentage'
                ? ($remainingBillAmount * $assignment->value / 100)
                : min($assignment->value, $remainingBillAmount);

            // Apply cap if set
            if ($assignment->cap_amount && $deduction > $assignment->cap_amount) {
                $deduction = $assignment->cap_amount;
            }

            $totalDeduction += $deduction;
            $appliedScholarships[] = [
                'scholarship_id' => $scholarship->id,
                'title' => $scholarship->title,
                'value_type' => $assignment->value_type,
                'value' => $assignment->value,
                'deduction' => $deduction
            ];

            // Update utilized amount
            $scholarship->increment('utilized_amount', $deduction);
        }

        return [
            'applied' => $totalDeduction > 0,
            'deduction' => $totalDeduction,
            'applied_scholarships' => $appliedScholarships
        ];
    }

    /**
     * Process scholarship application
     */
    public function processApplication($applicationId, $decision, $notes = null)
    {
        $application = ScholarshipApplication::findOrFail($applicationId);

        return DB::transaction(function () use ($application, $decision, $notes) {
            if ($decision === 'approve') {
                // Create scholarship assignment
                $assignment = ScholarshipAssignment::create([
                    'scholarship_id' => $application->scholarship_id,
                    'student_id' => $application->student_id,
                    'application_no' => $application->id,
                    'status' => 'active',
                    'approved_at' => now(),
                    'effective_from' => now(),
                    'effective_to' => now()->addYear(),
                    'value_type' => $application->scholarship->value_type,
                    'value' => $application->scholarship->value,
                    'cap_amount' => $application->scholarship->cap_amount,
                    'assigned_by' => Auth::id(),
                    'approved_by' => Auth::id(),
                ]);

                $application->update([
                    'status' => 'approved',
                    'reviewed_at' => now(),
                    'reviewed_by' => Auth::id(),
                    'admin_notes' => $notes
                ]);

                return $assignment;
            } else {
                $application->update([
                    'status' => 'rejected',
                    'reviewed_at' => now(),
                    'reviewed_by' => Auth::id(),
                    'rejection_reason' => $notes,
                    'admin_notes' => $notes
                ]);

                return null;
            }
        });
    }

    /**
     * Get scholarship summary for a student
     */
    public function getStudentScholarshipSummary($studentId)
    {
        $activeAssignments = ScholarshipAssignment::where('student_id', $studentId)
            ->where('status', 'active')
            ->where('effective_from', '<=', now())
            ->where(function($q) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', now());
            })
            ->with('scholarship')
            ->get();

        $totalSavings = 0;
        $summary = [];

        foreach ($activeAssignments as $assignment) {
            $totalSavings += $assignment->value;
            $summary[] = [
                'scholarship' => $assignment->scholarship->title,
                'type' => $assignment->scholarship->type->name ?? 'N/A',
                'value_type' => $assignment->value_type,
                'value' => $assignment->value,
                'effective_from' => $assignment->effective_from,
                'effective_to' => $assignment->effective_to,
            ];
        }

        return [
            'has_scholarship' => $activeAssignments->isNotEmpty(),
            'total_active' => $activeAssignments->count(),
            'total_savings' => $totalSavings,
            'scholarships' => $summary
        ];
    }
}
