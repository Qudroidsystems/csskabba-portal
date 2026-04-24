<?php
// app/Services/Payment/EnhancedPaymentService.php

namespace App\Services\Payment;

use App\Models\StudentBillPayment;
use App\Models\StudentBillPaymentRecord;
use App\Models\StudentBillPaymentBook;
use App\Models\PaymentBatch;
use App\Models\PaymentBatchItem;
use App\Models\SchoolBillTermSession;
use App\Models\SchoolBillModel;
use App\Models\Student;
use App\Services\Scholarship\ScholarshipService;
use App\Services\Discount\DiscountService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnhancedPaymentService
{
    protected $scholarshipService;
    protected $discountService;

    public function __construct(
        ScholarshipService $scholarshipService,
        DiscountService $discountService
    ) {
        $this->scholarshipService = $scholarshipService;
        $this->discountService = $discountService;
    }

    /**
     * Get complete payment details for a student including adjustments
     */
    public function getStudentPaymentDetails($studentId, $classId, $termId, $sessionId)
    {
        $student = Student::findOrFail($studentId);

        // Get all bills for this class/term/session
        $assignedBills = SchoolBillTermSession::where('class_id', $classId)
            ->where('termid_id', $termId)
            ->where('session_id', $sessionId)
            ->with('schoolBill')
            ->get();

        $bills = [];
        $totalOriginal = 0;
        $totalScholarshipDeduction = 0;
        $totalDiscountDeduction = 0;
        $totalAdjusted = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;

        foreach ($assignedBills as $assignment) {
            $bill = $assignment->schoolBill;

            // Get adjusted amount with scholarships and discounts
            $adjustment = $bill->getAdjustedAmountForStudent($studentId, $termId, $sessionId, $classId);

            // Get existing payment records
            $payment = StudentBillPayment::where('student_id', $studentId)
                ->where('school_bill_id', $bill->id)
                ->where('class_id', $classId)
                ->where('termid_id', $termId)
                ->where('session_id', $sessionId)
                ->first();

            $paidAmount = $payment ? $payment->total_paid : 0;
            $outstanding = max(0, $adjustment['adjusted_amount'] - $paidAmount);

            $bills[] = [
                'bill_id' => $bill->id,
                'title' => $bill->title,
                'description' => $bill->description,
                'category' => $bill->category,
                'original_amount' => $adjustment['original_amount'],
                'late_fee' => $adjustment['late_fee'] ?? 0,
                'scholarship_deduction' => $adjustment['scholarship_deduction'],
                'discount_deduction' => $adjustment['discount_deduction'],
                'adjusted_amount' => $adjustment['adjusted_amount'],
                'savings' => $adjustment['savings'],
                'paid_amount' => $paidAmount,
                'outstanding' => $outstanding,
                'is_completed' => $outstanding <= 0,
                'payment_id' => $payment ? $payment->id : null,
            ];

            $totalOriginal += $adjustment['original_amount'];
            $totalScholarshipDeduction += $adjustment['scholarship_deduction'];
            $totalDiscountDeduction += $adjustment['discount_deduction'];
            $totalAdjusted += $adjustment['adjusted_amount'];
            $totalPaid += $paidAmount;
            $totalOutstanding += $outstanding;
        }

        return [
            'student' => $student,
            'bills' => $bills,
            'summary' => [
                'total_original' => $totalOriginal,
                'total_scholarship_savings' => $totalScholarshipDeduction,
                'total_discount_savings' => $totalDiscountDeduction,
                'total_savings' => $totalScholarshipDeduction + $totalDiscountDeduction,
                'total_adjusted' => $totalAdjusted,
                'total_paid' => $totalPaid,
                'total_outstanding' => $totalOutstanding,
                'completion_percentage' => $totalAdjusted > 0 ? ($totalPaid / $totalAdjusted) * 100 : 0,
            ],
            'has_outstanding' => $totalOutstanding > 0
        ];
    }

    /**
     * Process payment with scholarship and discount considerations
     */
    public function processPayment($studentId, $classId, $termId, $sessionId, $paymentData)
    {
        return DB::transaction(function () use ($studentId, $classId, $termId, $sessionId, $paymentData) {
            // Get student's complete payment details
            $details = $this->getStudentPaymentDetails($studentId, $classId, $termId, $sessionId);

            $totalAmount = array_sum(array_column($paymentData['items'], 'amount'));

            if ($totalAmount <= 0) {
                throw new \Exception('Payment amount must be greater than zero');
            }

            // Verify that payment amounts don't exceed outstanding balances
            foreach ($paymentData['items'] as $item) {
                $bill = collect($details['bills'])->firstWhere('bill_id', $item['bill_id']);
                if (!$bill) {
                    throw new \Exception("Bill {$item['bill_id']} not found for this student");
                }

                if ($item['amount'] > $bill['outstanding']) {
                    throw new \Exception("Payment amount for {$bill['title']} cannot exceed outstanding balance of ₦" . number_format($bill['outstanding'], 2));
                }
            }

            // Create payment batch
            $batch = PaymentBatch::create([
                'batch_no' => PaymentBatch::generateBatchNumber(),
                'student_id' => $studentId,
                'payment_date' => now(),
                'total_amount' => $totalAmount,
                'payment_method' => $paymentData['payment_method'],
                'reference_no' => $paymentData['reference_no'] ?? null,
                'status' => 'completed',
                'notes' => $paymentData['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $processedItems = [];

            foreach ($paymentData['items'] as $item) {
                $billDetails = collect($details['bills'])->firstWhere('bill_id', $item['bill_id']);

                // Get or create payment record
                $payment = StudentBillPayment::firstOrCreate(
                    [
                        'student_id' => $studentId,
                        'school_bill_id' => $item['bill_id'],
                        'class_id' => $classId,
                        'termid_id' => $termId,
                        'session_id' => $sessionId,
                    ],
                    [
                        'payment_method' => $paymentData['payment_method'],
                        'payment_status' => 'pending',
                        'total_paid' => 0,
                        'total_balance' => $billDetails['adjusted_amount'],
                        'generated_by' => Auth::id(),
                        'delete_status' => '1',
                    ]
                );

                $balanceBefore = $payment->total_balance;
                $amountToPay = min($item['amount'], $balanceBefore);

                if ($amountToPay <= 0) {
                    continue;
                }

                $balanceAfter = $balanceBefore - $amountToPay;
                $newTotalPaid = $payment->total_paid + $amountToPay;
                $newStatus = $balanceAfter <= 0 ? 'completed' : 'partial';

                // Update payment record
                $payment->update([
                    'total_paid' => $newTotalPaid,
                    'total_balance' => $balanceAfter,
                    'payment_status' => $newStatus,
                    'last_payment_date' => now(),
                    'payment_method' => $paymentData['payment_method'],
                ]);

                // Create payment record detail
                $paymentRecord = StudentBillPaymentRecord::create([
                    'student_bill_payment_id' => $payment->id,
                    'class_id' => $classId,
                    'termid_id' => $termId,
                    'session_id' => $sessionId,
                    'amount_paid' => $amountToPay,
                    'last_payment' => $amountToPay,
                    'amount_owed' => $balanceAfter,
                    'total_bill' => $billDetails['adjusted_amount'],
                    'complete_payment' => $balanceAfter <= 0 ? 1 : 0,
                    'generated_by' => Auth::id(),
                    'invoiceNo' => null,
                ]);

                // Update payment book with scholarship/discount info
                $paymentBook = StudentBillPaymentBook::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'school_bill_id' => $item['bill_id'],
                        'class_id' => $classId,
                        'term_id' => $termId,
                        'session_id' => $sessionId,
                    ],
                    [
                        'amount_paid' => DB::raw("amount_paid + {$amountToPay}"),
                        'amount_owed' => $balanceAfter,
                        'payment_status' => $newStatus,
                        'generated_by' => Auth::id(),
                        'original_amount' => $billDetails['original_amount'],
                        'scholarship_deduction' => $billDetails['scholarship_deduction'],
                        'discount_deduction' => $billDetails['discount_deduction'],
                        'adjusted_amount' => $billDetails['adjusted_amount'],
                    ]
                );

                // Create batch item
                $batchItem = PaymentBatchItem::create([
                    'payment_batch_id' => $batch->id,
                    'school_bill_id' => $item['bill_id'],
                    'class_id' => $classId,
                    'termid_id' => $termId,
                    'session_id' => $sessionId,
                    'original_amount' => $billDetails['original_amount'],
                    'scholarship_deduction' => $billDetails['scholarship_deduction'],
                    'discount_deduction' => $billDetails['discount_deduction'],
                    'adjusted_amount' => $billDetails['adjusted_amount'],
                    'amount_paid' => $amountToPay,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'student_bill_payment_id' => $payment->id,
                ]);

                $processedItems[] = [
                    'bill_id' => $item['bill_id'],
                    'title' => $billDetails['title'],
                    'original_amount' => $billDetails['original_amount'],
                    'savings' => $billDetails['savings'],
                    'adjusted_amount' => $billDetails['adjusted_amount'],
                    'amount_paid' => $amountToPay,
                    'balance_after' => $balanceAfter,
                    'is_completed' => $balanceAfter <= 0,
                ];
            }

            // Generate receipt data
            $receipt = $this->generateReceipt($batch, $processedItems, $studentId, $details['summary']);

            $batch->update(['receipt_data' => $receipt]);

            return [
                'success' => true,
                'batch' => $batch,
                'processed_items' => $processedItems,
                'receipt' => $receipt,
                'total_paid' => $totalAmount,
                'total_savings' => $details['summary']['total_savings'],
            ];
        });
    }

    /**
     * Generate receipt with scholarship/discount information
     */
    protected function generateReceipt($batch, $items, $studentId, $summary)
    {
        $student = Student::find($studentId);

        return [
            'receipt_no' => 'RCP-' . $batch->batch_no,
            'date' => $batch->payment_date,
            'student_name' => $student->firstname . ' ' . $student->lastname,
            'admission_no' => $student->admissionNo,
            'payment_method' => $batch->payment_method,
            'reference_no' => $batch->reference_no,
            'items' => $items,
            'summary' => $summary,
            'total_amount' => $batch->total_amount,
            'total_savings' => $summary['total_savings'],
            'amount_in_words' => $this->convertNumberToWords($batch->total_amount),
        ];
    }

    /**
     * Convert number to words
     */
    protected function convertNumberToWords($number)
    {
        $words = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        return ucfirst($words->format($number)) . ' Naira Only';
    }

    /**
     * Reverse a payment batch
     */
    public function reversePayment($batchId, $reason)
    {
        return DB::transaction(function () use ($batchId, $reason) {
            $batch = PaymentBatch::findOrFail($batchId);

            if ($batch->status !== 'completed') {
                throw new \Exception('Only completed payments can be reversed');
            }

            foreach ($batch->items as $item) {
                $payment = StudentBillPayment::find($item->student_bill_payment_id);

                if ($payment) {
                    $newTotalPaid = $payment->total_paid - $item->amount_paid;
                    $newBalance = $payment->total_balance + $item->amount_paid;

                    $payment->update([
                        'total_paid' => $newTotalPaid,
                        'total_balance' => $newBalance,
                        'payment_status' => $newBalance <= 0 ? 'completed' : ($newTotalPaid > 0 ? 'partial' : 'pending'),
                    ]);

                    // Create reversal record
                    StudentBillPaymentRecord::create([
                        'student_bill_payment_id' => $payment->id,
                        'class_id' => $item->class_id,
                        'termid_id' => $item->termid_id,
                        'session_id' => $item->session_id,
                        'amount_paid' => -$item->amount_paid,
                        'last_payment' => 0,
                        'amount_owed' => $newBalance,
                        'total_bill' => $item->adjusted_amount,
                        'complete_payment' => 0,
                        'generated_by' => Auth::id(),
                        'is_reversal' => true,
                        'reversal_reason' => $reason,
                    ]);

                    // Update payment book
                    $paymentBook = StudentBillPaymentBook::where('student_id', $batch->student_id)
                        ->where('school_bill_id', $item->school_bill_id)
                        ->where('class_id', $item->class_id)
                        ->where('term_id', $item->termid_id)
                        ->where('session_id', $item->session_id)
                        ->first();

                    if ($paymentBook) {
                        $paymentBook->update([
                            'amount_paid' => $paymentBook->amount_paid - $item->amount_paid,
                            'amount_owed' => $paymentBook->amount_owed + $item->amount_paid,
                            'payment_status' => $newBalance <= 0 ? 'completed' : 'partial',
                        ]);
                    }
                }
            }

            $batch->update([
                'status' => 'reversed',
                'reversal_reason' => $reason,
                'reversed_by' => Auth::id(),
                'reversed_at' => now(),
            ]);

            return $batch;
        });
    }
}
