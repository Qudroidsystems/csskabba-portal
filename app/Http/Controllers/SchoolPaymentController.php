<?php

namespace App\Http\Controllers;

use PDF;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\Schoolterm;
use App\Models\Schoolclass;
use Illuminate\Http\Request;
use App\Models\Schoolsession;
use App\Models\SchoolBillModel;
use App\Models\SchoolInformation;
use App\Models\StudentBillPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\SchoolBillTermSession;
use App\Models\StudentBillPaymentBook;
use App\Models\StudentBillPaymentRecord;
use App\Models\ScholarshipAssignment;
use App\Models\DiscountAssignment;
use App\Models\Scholarship;
use App\Models\Discount;
use App\Models\Studentpicture;

class SchoolPaymentController extends Controller
{
    /**
     * Display student list for payment selection.
     */
    public function index(Request $request)
    {
        $pagetitle = 'Student Payments';

        $student = Student::leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->where('schoolsession.status', 'Current')
            ->select([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionNo',
                'studentRegistration.firstname as firstname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.othername as othername',
                'studentRegistration.gender as gender',
                'studentRegistration.student_status as student_status',
                'schoolclass.id as schoolclassid',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as arm',
                'schoolterm.term as term',
                'schoolsession.session as session',
                'studentpicture.picture as picture',
            ])
            ->get();

        // Bulk-fetch scholarship and discount student IDs to avoid N+1 per student
        $studentIds = $student->pluck('id')->toArray();
        $now = now();

        $scholarshipStudentIds = ScholarshipAssignment::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
            })
            ->pluck('student_id')
            ->flip()
            ->toArray();

        $discountStudentIds = DiscountAssignment::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
            })
            ->pluck('student_id')
            ->flip()
            ->toArray();

        foreach ($student as $s) {
            $s->has_scholarship = isset($scholarshipStudentIds[$s->id]);
            $s->has_discount    = isset($discountStudentIds[$s->id]);
            $s->full_name       = $this->getFullNameWithOther($s->firstname, $s->lastname, $s->othername);
        }

        return view('schoolpayment.index', compact('pagetitle', 'student'));
    }

    /**
     * Show payment details page (called from debtors list)
     */
    public function showPaymentDetails($studentId, $classId, $termId, $sessionId)
    {
        $pagetitle = 'Payment Details';
        $student   = Student::findOrFail($studentId);

        return view('payment.payment-details', compact(
            'pagetitle', 'studentId', 'classId', 'termId', 'sessionId'
        ));
    }

    /**
     * Termsession payments page (alternative entry point)
     */
    public function termsessionpayments(Request $request)
    {
        $studentId = (int) $request->get('studentId');
        $termid    = (int) $request->get('termid');
        $sessionid = (int) $request->get('sessionid');

        if (!$studentId || !$termid || !$sessionid) {
            return redirect()->route('schoolpayment.index')
                ->with('error', 'Invalid student, term, or session selected.');
        }

        $pagetitle = 'Student Payment Details';

        return view('schoolpayment.studentpayment', compact('pagetitle', 'studentId', 'termid', 'sessionid'));
    }

    /**
     * Term/Session selector page
     */
    public function termSession(string $id)
    {
        $pagetitle      = 'Student Payments';
        $schoolterms    = Schoolterm::all();
        $schoolsessions = Schoolsession::all();

        $studentDetails = Student::leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->where('studentRegistration.id', $id)
            ->select('studentRegistration.*', 'studentpicture.picture as avatar')
            ->first();

        return view('schoolpayment.termSession', compact('pagetitle', 'schoolterms', 'schoolsessions', 'id', 'studentDetails'));
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function getStudentAvatarUrl($picture): ?string
    {
        if (!$picture || $picture === 'unnamed.jpg' || $picture === '') {
            return null;
        }
        return asset('storage/images/student_avatars/' . $picture);
    }

    private function getFullNameWithOther($firstname, $lastname, $othername = ''): string
    {
        $fullName = trim($firstname . ' ' . $lastname);
        if (!empty($othername)) {
            $fullName .= ' (' . $othername . ')';
        }
        return $fullName;
    }

    // ── Scholarship / discount helpers ────────────────────────────────────

    /**
     * FIX #6: Accept pre-fetched assignment to avoid N+1 inside loops.
     * Falls back to a DB query when called standalone (e.g. from store()).
     */
    private function getScholarshipDeduction(int $studentId, float $billAmount, $preloaded = null): array
    {
        $assignment = $preloaded ?? ScholarshipAssignment::where('student_id', $studentId)
            ->where('status', 'active')
            ->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', now());
            })
            ->with('scholarship')
            ->first();

        if (!$assignment || !$assignment->scholarship) {
            return ['deduction' => 0, 'label' => null, 'assignment' => null];
        }

        $scholarship = $assignment->scholarship;
        $deduction   = 0;

        if ($assignment->value_type === 'percentage') {
            $deduction = ($billAmount * $assignment->value / 100);
            if ($assignment->cap_amount && $deduction > $assignment->cap_amount) {
                $deduction = $assignment->cap_amount;
            }
        } else {
            $deduction = min($assignment->value, $billAmount);
        }

        return [
            'deduction'  => round($deduction, 2),
            'label'      => $scholarship->title,
            'value_type' => $assignment->value_type,
            'value'      => $assignment->value,
            'assignment' => $assignment,
        ];
    }

    /**
     * FIX #6: Accept pre-fetched assignments collection to avoid N+1 inside loops.
     */
    private function getDiscountDeduction(int $studentId, int $billId, float $billAmount, float $scholarshipDeduction = 0, $preloaded = null): array
    {
        $assignments = $preloaded ?? DiscountAssignment::where('student_id', $studentId)
            ->where('status', 'active')
            ->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', now());
            })
            ->with('discount')
            ->get();

        $totalDeduction = 0;
        $labels         = [];
        $remaining      = $billAmount - $scholarshipDeduction;

        foreach ($assignments as $assignment) {
            if (!$assignment->discount || $assignment->discount->status !== 'active') continue;

            if (method_exists($assignment->discount, 'appliesToBill')) {
                if (!$assignment->discount->appliesToBill($billId)) continue;
            }

            $deduction = 0;
            if ($assignment->value_type === 'percentage') {
                $deduction = ($remaining * $assignment->value / 100);
                if ($assignment->max_amount && $deduction > $assignment->max_amount) {
                    $deduction = $assignment->max_amount;
                }
            } else {
                $deduction = min($assignment->value, $remaining);
            }

            $totalDeduction += $deduction;
            $labels[]        = $assignment->discount->title;
            $remaining      -= $deduction;
            if ($remaining <= 0) break;
        }

        return [
            'deduction' => round(min($totalDeduction, $remaining + $totalDeduction), 2),
            'labels'    => $labels,
        ];
    }

    /**
     * FIX #6: Accept pre-fetched scholarship/discount to skip redundant queries in loops.
     */
    private function buildBillAdjustment(int $studentId, int $billId, float $originalAmount, $scholarshipAssignment = null, $discountAssignments = null): array
    {
        $scholarship = $this->getScholarshipDeduction($studentId, $originalAmount, $scholarshipAssignment);
        $discount    = $this->getDiscountDeduction($studentId, $billId, $originalAmount, $scholarship['deduction'], $discountAssignments);

        $totalDeduction = $scholarship['deduction'] + $discount['deduction'];
        $adjustedAmount = max(0, $originalAmount - $totalDeduction);

        return [
            'original_amount'       => $originalAmount,
            'scholarship_deduction' => $scholarship['deduction'],
            'scholarship_label'     => $scholarship['label'],
            'discount_deduction'    => $discount['deduction'],
            'discount_labels'       => $discount['labels'],
            'total_savings'         => $totalDeduction,
            'adjusted_amount'       => $adjustedAmount,
        ];
    }

    private function studentHasScholarship(int $studentId): bool
    {
        return ScholarshipAssignment::where('student_id', $studentId)
            ->where('status', 'active')
            ->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', now());
            })
            ->exists();
    }

    private function studentHasDiscount(int $studentId): bool
    {
        return DiscountAssignment::where('student_id', $studentId)
            ->where('status', 'active')
            ->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', now());
            })
            ->exists();
    }

    // ── Fetch student data helper ─────────────────────────────────────────

    /**
     * FIX #9: Returns a status flag alongside the student so callers know
     * whether the result came from the requested term/session or from the
     * Current-session fallback, allowing them to warn the user.
     */
    private function fetchStudentData(int $studentId, int $termid, int $sessionid): ?object
    {
        $student = Student::where('studentRegistration.id', $studentId)
            ->leftJoin('studentclass', function ($join) use ($termid, $sessionid) {
                $join->on('studentclass.studentId', '=', 'studentRegistration.id')
                     ->where('studentclass.termid', $termid)
                     ->where('studentclass.sessionid', $sessionid);
            })
            ->leftJoin('parentRegistration', 'parentRegistration.id', '=', 'studentRegistration.id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->select([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionNo',
                'studentRegistration.firstname as firstname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.othername as othername',
                'studentRegistration.home_address2 as homeadd',
                'parentRegistration.father_phone as phone',
                'studentRegistration.statusId as statusId',
                'studentRegistration.student_status as student_status',
                'studentpicture.picture as avatar',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as arm',
                'schoolterm.term as term',
                'schoolsession.session as session',
                'studentclass.schoolclassid as schoolclassId',
            ])
            ->first();

        if ($student && $student->schoolclassId) {
            $student->used_fallback = false;
            return $student;
        }

        // FIX #9: Fallback is logged AND flagged so AJAX/views can surface a warning.
        Log::info('SchoolPayment: falling back to Current session for student', [
            'studentId' => $studentId,
            'termid'    => $termid,
            'sessionid' => $sessionid,
        ]);

        $student = Student::where('studentRegistration.id', $studentId)
            ->leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
            ->leftJoin('parentRegistration', 'parentRegistration.id', '=', 'studentRegistration.id')
            ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->leftJoin('schoolterm', 'schoolterm.id', '=', 'studentclass.termid')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->where('schoolsession.status', 'Current')
            ->select([
                'studentRegistration.id as id',
                'studentRegistration.admissionNo as admissionNo',
                'studentRegistration.firstname as firstname',
                'studentRegistration.lastname as lastname',
                'studentRegistration.othername as othername',
                'studentRegistration.home_address2 as homeadd',
                'parentRegistration.father_phone as phone',
                'studentRegistration.statusId as statusId',
                'studentRegistration.student_status as student_status',
                'studentpicture.picture as avatar',
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as arm',
                'schoolterm.term as term',
                'schoolsession.session as session',
                'studentclass.schoolclassid as schoolclassId',
            ])
            ->first();

        if ($student) {
            $student->used_fallback = true;
        }

        return $student;
    }

    // ── AJAX: Get payment details ─────────────────────────────────────────

    public function getPaymentDetailsAjax(Request $request)
    {
        try {
            $studentId = (int) $request->studentId;
            $termid    = (int) $request->termid;
            $sessionid = (int) $request->sessionid;

            if (!$studentId || !$termid || !$sessionid) {
                return response()->json(['success' => false, 'message' => 'Invalid parameters'], 400);
            }

            $studentdata = $this->fetchStudentData($studentId, $termid, $sessionid);

            if (!$studentdata) {
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }

            $schoolclassId = $studentdata->schoolclassId;

            $rawBills = DB::table('school_bill_class_term_session')
                ->where('school_bill_class_term_session.class_id', $schoolclassId)
                ->where('school_bill_class_term_session.termid_id', $termid)
                ->where('school_bill_class_term_session.session_id', $sessionid)
                ->whereNull('school_bill_class_term_session.deleted_at')
                ->join('school_bill', 'school_bill.id', '=', 'school_bill_class_term_session.bill_id')
                ->where(function ($q) use ($studentdata) {
                    $q->whereNull('school_bill.statusId')
                      ->orWhere('school_bill.statusId', '')
                      ->orWhere('school_bill.statusId', 0)
                      ->orWhere('school_bill.statusId', $studentdata->statusId);
                })
                ->select([
                    'school_bill_class_term_session.id as id',
                    'school_bill.id as schoolbillid',
                    'school_bill.title as title',
                    'school_bill.description as description',
                    'school_bill.bill_amount as amount',
                    'school_bill.statusId as billStatusId',
                ])
                ->get();

            // FIX #6: Fetch scholarship and discount assignments once, reuse in the loop.
            $now = now();
            $scholarshipAssignment = ScholarshipAssignment::where('student_id', $studentId)
                ->where('status', 'active')
                ->where('effective_from', '<=', $now)
                ->where(function ($q) use ($now) {
                    $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
                })
                ->with('scholarship')
                ->first();

            $discountAssignments = DiscountAssignment::where('student_id', $studentId)
                ->where('status', 'active')
                ->where('effective_from', '<=', $now)
                ->where(function ($q) use ($now) {
                    $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
                })
                ->with('discount')
                ->get();

            $student_bill_info = $rawBills->map(function ($bill) use ($studentId, $scholarshipAssignment, $discountAssignments) {
                $adj = $this->buildBillAdjustment(
                    $studentId,
                    $bill->schoolbillid,
                    (float) $bill->amount,
                    $scholarshipAssignment,
                    $discountAssignments
                );
                $bill->original_amount       = $adj['original_amount'];
                $bill->scholarship_deduction = $adj['scholarship_deduction'];
                $bill->scholarship_label     = $adj['scholarship_label'];
                $bill->discount_deduction    = $adj['discount_deduction'];
                $bill->discount_labels       = $adj['discount_labels'];
                $bill->total_savings         = $adj['total_savings'];
                $bill->adjusted_amount       = $adj['adjusted_amount'];
                return $bill;
            });

            $studentpaymentbillbook = StudentBillPaymentBook::where('student_id', $studentId)
                ->where('term_id', $termid)
                ->where('session_id', $sessionid)
                ->get();

            $billsData     = [];
            $totalAdjusted = 0;
            $totalPaid     = 0;

            foreach ($student_bill_info as $bill) {
                $bookEntry   = $studentpaymentbillbook->where('school_bill_id', $bill->schoolbillid)->first();
                $amountPaid  = $bookEntry ? (float) $bookEntry->amount_paid : 0;
                $adjustedAmt = (float) $bill->adjusted_amount;
                $balance     = max(0, $adjustedAmt - $amountPaid);
                $progress    = $adjustedAmt > 0 ? min(100, ($amountPaid / $adjustedAmt) * 100) : 0;

                $totalAdjusted += $adjustedAmt;
                $totalPaid     += $amountPaid;

                $pendingPayment = StudentBillPayment::where('student_id', $studentId)
                    ->where('school_bill_id', $bill->schoolbillid)
                    ->where('termid_id', $termid)
                    ->where('session_id', $sessionid)
                    ->where('delete_status', '1')
                    ->first();

                $billsData[] = [
                    'id'                    => $bill->schoolbillid,
                    'class_id'              => $schoolclassId,
                    'title'                 => $bill->title,
                    'description'           => $bill->description,
                    'original_amount'       => $bill->original_amount,
                    'adjusted_amount'       => $adjustedAmt,
                    'amount_paid'           => $amountPaid,
                    'balance'               => $balance,
                    'progress'              => $progress,
                    'scholarship_deduction' => $bill->scholarship_deduction,
                    'discount_deduction'    => $bill->discount_deduction,
                    'total_savings'         => $bill->total_savings,
                    'scholarship_label'     => $bill->scholarship_label,
                    'discount_labels'       => is_array($bill->discount_labels)
                                                ? implode(', ', $bill->discount_labels)
                                                : ($bill->discount_labels ?? ''),
                    'has_pending_invoice'   => !is_null($pendingPayment),
                    'is_paid'               => $balance <= 0 && $amountPaid > 0,
                    'is_partial'            => $amountPaid > 0 && $balance > 0,
                ];
            }

            // Payment records (pending / not yet invoiced)
            $paymentRecords = StudentBillPayment::where('student_bill_payment.student_id', $studentId)
                ->where('student_bill_payment.termid_id', $termid)
                ->where('student_bill_payment.session_id', $sessionid)
                ->where('student_bill_payment.delete_status', '1')
                ->leftJoin('student_bill_payment_record', function ($join) {
                    $join->on('student_bill_payment_record.student_bill_payment_id', '=', 'student_bill_payment.id')
                        ->whereRaw('student_bill_payment_record.id = (
                            SELECT MAX(id) FROM student_bill_payment_record spr
                            WHERE spr.student_bill_payment_id = student_bill_payment.id
                        )');
                })
                ->leftJoin('school_bill', 'school_bill.id', '=', 'student_bill_payment.school_bill_id')
                ->leftJoin('users', 'users.id', '=', 'student_bill_payment.generated_by')
                ->select([
                    'student_bill_payment.id as paymentId',
                    'student_bill_payment.school_bill_id as school_bill_id',
                    'student_bill_payment_record.id as recordId',
                    'student_bill_payment.created_at as receivedDate',
                    'student_bill_payment.payment_method as paymentMethod',
                    'student_bill_payment.status as paymentStatus',
                    'school_bill.title as title',
                    'school_bill.description as description',
                    'school_bill.bill_amount as billAmount',
                    'student_bill_payment_record.amount_paid as totalAmountPaid',
                    'student_bill_payment_record.amount_owed as balance',
                    DB::raw('COALESCE(users.name, "Unknown") as receivedBy'),
                ])
                ->get();

            // Payment history (invoiced / completed)
            $paymentHistory = StudentBillPayment::where('student_bill_payment.student_id', $studentId)
                ->where('student_bill_payment.termid_id', $termid)
                ->where('student_bill_payment.session_id', $sessionid)
                ->where('student_bill_payment.delete_status', '0')
                ->leftJoin('student_bill_payment_record', 'student_bill_payment_record.student_bill_payment_id', '=', 'student_bill_payment.id')
                ->leftJoin('school_bill', 'school_bill.id', '=', 'student_bill_payment.school_bill_id')
                ->leftJoin('users', 'users.id', '=', 'student_bill_payment.generated_by')
                ->select([
                    'student_bill_payment.id as paymentId',
                    'student_bill_payment.school_bill_id as school_bill_id',
                    'student_bill_payment.class_id as classId',
                    'student_bill_payment.termid_id as termId',
                    'student_bill_payment.session_id as sessionId',
                    'student_bill_payment_record.id as recordId',
                    'student_bill_payment_record.created_at as receivedDate',
                    'student_bill_payment.payment_method as paymentMethod',
                    DB::raw('CASE WHEN student_bill_payment_record.complete_payment = 1 THEN "Completed" ELSE "Pending" END as paymentStatus'),
                    'school_bill.title as title',
                    'school_bill.description as description',
                    'school_bill.bill_amount as billAmount',
                    'student_bill_payment_record.amount_paid as totalAmountPaid',
                    'student_bill_payment_record.amount_owed as balance',
                    DB::raw('COALESCE(users.name, "Unknown") as receivedBy'),
                    'student_bill_payment_record.complete_payment as completePayment',
                ])
                ->orderBy('student_bill_payment_record.created_at', 'desc')
                ->get();

            $totalOutstanding = max(0, $totalAdjusted - $totalPaid);
            $totalSavings     = $student_bill_info->sum('total_savings');
            $totalOriginal    = $student_bill_info->sum('original_amount');

            $fullName = $this->getFullNameWithOther($studentdata->firstname, $studentdata->lastname, $studentdata->othername ?? '');

            return response()->json([
                'success'      => true,
                'used_fallback' => $studentdata->used_fallback ?? false,
                'data'         => [
                    'student' => [
                        'id'             => $studentdata->id,
                        'name'           => $fullName,
                        'firstname'      => $studentdata->firstname,
                        'lastname'       => $studentdata->lastname,
                        'othername'      => $studentdata->othername ?? '',
                        'admissionNo'    => $studentdata->admissionNo,
                        'avatar'         => $studentdata->avatar,
                        'schoolclass'    => $studentdata->schoolclass,
                        'schoolclassId'  => $schoolclassId,
                        'arm'            => $studentdata->arm,
                        'student_status' => $studentdata->student_status,
                        'statusId'       => $studentdata->statusId,
                    ],
                    'bills'           => $billsData,
                    'payment_records' => $paymentRecords,
                    'payment_history' => $paymentHistory,
                    'scholarship'     => $scholarshipAssignment ? [
                        'title'        => $scholarshipAssignment->scholarship->title ?? 'Scholarship',
                        'value'        => $scholarshipAssignment->value,
                        'value_type'   => $scholarshipAssignment->value_type,
                        'effective_to' => $scholarshipAssignment->effective_to,
                    ] : null,
                    'discounts' => $discountAssignments->map(function ($da) {
                        return $da->discount ? [
                            'title'      => $da->discount->title,
                            'value'      => $da->value,
                            'value_type' => $da->value_type,
                        ] : null;
                    })->filter()->values(),
                    'totals' => [
                        'original'    => $totalOriginal,
                        'adjusted'    => $totalAdjusted,
                        'paid'        => $totalPaid,
                        'outstanding' => $totalOutstanding,
                        'savings'     => $totalSavings,
                    ],
                    'term'    => optional(Schoolterm::find($termid))->term,
                    'session' => optional(Schoolsession::find($sessionid))->session,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('getPaymentDetailsAjax error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Store individual payment ──────────────────────────────────────────

    public function store(Request $request)
    {
        try {
            $request->validate([
                'student_id'            => 'required|integer|exists:studentRegistration,id',
                'class_id'              => 'required|integer|exists:schoolclass,id',
                'term_id'               => 'required|integer|exists:schoolterm,id',
                'session_id'            => 'required|integer|exists:schoolsession,id',
                'school_bill_id'        => 'required|integer|exists:school_bill,id',
                'actual_amount'         => 'required|numeric|min:0.01',
                'adjusted_amount'       => 'nullable|numeric|min:0',
                'balance2'              => 'required|numeric|min:0',
                'last_amount_paid'      => 'required|numeric|min:0',
                'payment_amount'        => 'required|numeric|min:0.01',
                'payment_amount2'       => 'nullable|numeric|min:0.01',
                'payment_method2'       => 'required|string|in:Bank Deposit,School POS,Bank Transfer,Cheque',
                'scholarship_deduction' => 'nullable|numeric|min:0',
                'discount_deduction'    => 'nullable|numeric|min:0',
            ]);

            if (!Auth::check()) {
                return response()->json(['success' => false, 'message' => 'Not authenticated. Please login again.'], 401);
            }

            DB::beginTransaction();

            $studentPayment = StudentBillPayment::where([
                'student_id'     => $request->student_id,
                'school_bill_id' => $request->school_bill_id,
                'class_id'       => $request->class_id,
                'termid_id'      => $request->term_id,
                'session_id'     => $request->session_id,
            ])->first();

            if ($studentPayment && $studentPayment->delete_status == '1') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot make additional payments until the pending invoice is generated for this bill.',
                ], 422);
            }

            if ($studentPayment) {
                $records   = StudentBillPaymentRecord::where('student_bill_payment_id', $studentPayment->id)->get();
                $totalPaid = $records->sum('amount_paid');
                $billAmt   = $request->adjusted_amount ?: $request->actual_amount;
                if ($totalPaid >= $billAmt) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'This bill is already fully paid.'], 422);
                }
            }

            $bill = SchoolBillModel::findOrFail($request->school_bill_id);

            $adj                 = $this->buildBillAdjustment($request->student_id, $request->school_bill_id, (float) $bill->bill_amount);
            $finalAdjustedAmount = $adj['adjusted_amount'] > 0
                ? $adj['adjusted_amount']
                : ($request->adjusted_amount ?: $bill->bill_amount);

            $paymentAmount = (float) $request->payment_amount;
            $balance       = (float) $request->balance2 - $paymentAmount;

            if ($balance < 0) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Payment amount exceeds the outstanding balance.'], 422);
            }

            $completePayment = $balance <= 0 ? 1 : 0;
            $status          = $completePayment ? 'Completed' : 'Pending';
            $generatedBy     = Auth::id();

            if ($studentPayment) {
                DB::table('student_bill_payment')
                    ->where('id', $studentPayment->id)
                    ->update([
                        'payment_method' => $request->payment_method2,
                        'status'         => $status,
                        'generated_by'   => $generatedBy,
                        'delete_status'  => '1',
                        'updated_at'     => now(),
                    ]);

                StudentBillPaymentRecord::create([
                    'student_bill_payment_id' => $studentPayment->id,
                    'class_id'                => $request->class_id,
                    'termid_id'               => $request->term_id,
                    'session_id'              => $request->session_id,
                    'amount_paid'             => $paymentAmount,
                    'last_payment'            => $paymentAmount,
                    'amount_owed'             => $balance,
                    'total_bill'              => $finalAdjustedAmount,
                    'complete_payment'        => $completePayment,
                    'generated_by'            => $generatedBy,
                ]);

                $paymentBook = StudentBillPaymentBook::where([
                    'student_id'     => $request->student_id,
                    'school_bill_id' => $request->school_bill_id,
                    'class_id'       => $request->class_id,
                    'term_id'        => $request->term_id,
                    'session_id'     => $request->session_id,
                ])->first();

                if ($paymentBook) {
                    DB::table('student_bill_payment_book')
                        ->where('id', $paymentBook->id)
                        ->update([
                            'amount_paid'           => DB::raw('amount_paid + ' . $paymentAmount),
                            // FIX #7: derive amount_owed from adjusted total minus all paid so far
                            'amount_owed'           => max(0, $finalAdjustedAmount - ($paymentBook->amount_paid + $paymentAmount)),
                            'payment_status'        => $status,
                            'generated_by'          => $generatedBy,
                            'scholarship_deduction' => $adj['scholarship_deduction'],
                            'discount_deduction'    => $adj['discount_deduction'],
                            'adjusted_amount'       => $finalAdjustedAmount,
                            'updated_at'            => now(),
                        ]);
                } else {
                    StudentBillPaymentBook::create([
                        'student_id'            => $request->student_id,
                        'school_bill_id'        => $request->school_bill_id,
                        'class_id'              => $request->class_id,
                        'term_id'               => $request->term_id,
                        'session_id'            => $request->session_id,
                        'amount_paid'           => $paymentAmount,
                        'amount_owed'           => max(0, $finalAdjustedAmount - $paymentAmount),
                        'payment_status'        => $status,
                        'generated_by'          => $generatedBy,
                        'original_amount'       => $bill->bill_amount,
                        'scholarship_deduction' => $adj['scholarship_deduction'],
                        'discount_deduction'    => $adj['discount_deduction'],
                        'adjusted_amount'       => $finalAdjustedAmount,
                    ]);
                }
            } else {
                $studentPayment = StudentBillPayment::create([
                    'student_id'     => $request->student_id,
                    'school_bill_id' => $request->school_bill_id,
                    'class_id'       => $request->class_id,
                    'termid_id'      => $request->term_id,
                    'session_id'     => $request->session_id,
                    'payment_method' => $request->payment_method2,
                    'status'         => $status,
                    'generated_by'   => $generatedBy,
                    'delete_status'  => '1',
                ]);

                StudentBillPaymentRecord::create([
                    'student_bill_payment_id' => $studentPayment->id,
                    'class_id'                => $request->class_id,
                    'termid_id'               => $request->term_id,
                    'session_id'              => $request->session_id,
                    'amount_paid'             => $paymentAmount,
                    'last_payment'            => $paymentAmount,
                    'amount_owed'             => max(0, $finalAdjustedAmount - $paymentAmount),
                    'total_bill'              => $finalAdjustedAmount,
                    'complete_payment'        => $completePayment,
                    'generated_by'            => $generatedBy,
                ]);

                StudentBillPaymentBook::create([
                    'student_id'            => $request->student_id,
                    'school_bill_id'        => $request->school_bill_id,
                    'class_id'              => $request->class_id,
                    'term_id'               => $request->term_id,
                    'session_id'            => $request->session_id,
                    'amount_paid'           => $paymentAmount,
                    'amount_owed'           => max(0, $finalAdjustedAmount - $paymentAmount),
                    'payment_status'        => $status,
                    'generated_by'          => $generatedBy,
                    'original_amount'       => $bill->bill_amount,
                    'scholarship_deduction' => $adj['scholarship_deduction'],
                    'discount_deduction'    => $adj['discount_deduction'],
                    'adjusted_amount'       => $finalAdjustedAmount,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment recorded successfully.']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment store error: ', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to record payment: ' . $e->getMessage()], 500);
        }
    }

    // ── Bulk store payment ────────────────────────────────────────────────

    public function bulkStore(Request $request)
    {
        try {
            $request->validate([
                'student_id'                            => 'required|integer|exists:studentRegistration,id',
                'class_id'                              => 'required|integer|exists:schoolclass,id',
                'term_id'                               => 'required|integer|exists:schoolterm,id',
                'session_id'                            => 'required|integer|exists:schoolsession,id',
                'payment_amount'                        => 'required|numeric|min:0.01',
                'payment_method'                        => 'required|string|in:Bank Deposit,School POS,Bank Transfer,Cheque',
                // FIX #8: title is optional but now explicitly declared
                'bill_payments'                         => 'required|array|min:1',
                'bill_payments.*.school_bill_id'        => 'required|integer|exists:school_bill,id',
                'bill_payments.*.title'                 => 'nullable|string|max:255',
                'bill_payments.*.adjusted_amount'       => 'required|numeric|min:0',
                'bill_payments.*.balance'               => 'required|numeric|min:0',
                'bill_payments.*.scholarship_deduction' => 'nullable|numeric|min:0',
                'bill_payments.*.discount_deduction'    => 'nullable|numeric|min:0',
            ]);

            if (!Auth::check()) {
                return response()->json(['success' => false, 'message' => 'Not authenticated. Please login again.'], 401);
            }

            DB::beginTransaction();

            $generatedBy        = Auth::id();
            $totalPaymentAmount = (float) $request->payment_amount;
            $remainingAmount    = $totalPaymentAmount;
            $paymentsProcessed  = [];

            foreach ($request->bill_payments as $billPayment) {
                if ($remainingAmount <= 0) break;

                $schoolBillId         = $billPayment['school_bill_id'];
                $adjustedAmount       = (float) $billPayment['adjusted_amount'];
                $currentBalance       = (float) $billPayment['balance'];
                $scholarshipDeduction = (float) ($billPayment['scholarship_deduction'] ?? 0);
                $discountDeduction    = (float) ($billPayment['discount_deduction'] ?? 0);

                if ($currentBalance <= 0) continue;

                $paymentForThisBill = min($remainingAmount, $currentBalance);
                if ($paymentForThisBill <= 0) continue;

                $remainingAmount -= $paymentForThisBill;
                $newBalance       = $currentBalance - $paymentForThisBill;
                $completePayment  = $newBalance <= 0 ? 1 : 0;
                $status           = $completePayment ? 'Completed' : 'Pending';

                $studentPayment = StudentBillPayment::where([
                    'student_id'     => $request->student_id,
                    'school_bill_id' => $schoolBillId,
                    'class_id'       => $request->class_id,
                    'termid_id'      => $request->term_id,
                    'session_id'     => $request->session_id,
                ])->first();

                if ($studentPayment && $studentPayment->delete_status == '1') {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot make payment — a pending invoice exists for one of the selected bills.',
                    ], 422);
                }

                if ($studentPayment) {
                    DB::table('student_bill_payment')
                        ->where('id', $studentPayment->id)
                        ->update([
                            'payment_method' => $request->payment_method,
                            'status'         => $status,
                            'generated_by'   => $generatedBy,
                            'delete_status'  => '1',
                            'updated_at'     => now(),
                        ]);

                    StudentBillPaymentRecord::create([
                        'student_bill_payment_id' => $studentPayment->id,
                        'class_id'                => $request->class_id,
                        'termid_id'               => $request->term_id,
                        'session_id'              => $request->session_id,
                        'amount_paid'             => $paymentForThisBill,
                        'last_payment'            => $paymentForThisBill,
                        'amount_owed'             => $newBalance,
                        'total_bill'              => $adjustedAmount,
                        'complete_payment'        => $completePayment,
                        'generated_by'            => $generatedBy,
                    ]);

                    $paymentBook = StudentBillPaymentBook::where([
                        'student_id'     => $request->student_id,
                        'school_bill_id' => $schoolBillId,
                        'class_id'       => $request->class_id,
                        'term_id'        => $request->term_id,
                        'session_id'     => $request->session_id,
                    ])->first();

                    if ($paymentBook) {
                        // FIX #7: re-derive amount_owed from the adjusted total, not a running delta
                        $newTotalPaid = $paymentBook->amount_paid + $paymentForThisBill;
                        DB::table('student_bill_payment_book')
                            ->where('id', $paymentBook->id)
                            ->update([
                                'amount_paid'           => $newTotalPaid,
                                'amount_owed'           => max(0, $adjustedAmount - $newTotalPaid),
                                'payment_status'        => $status,
                                'generated_by'          => $generatedBy,
                                'scholarship_deduction' => $scholarshipDeduction,
                                'discount_deduction'    => $discountDeduction,
                                'adjusted_amount'       => $adjustedAmount,
                                'updated_at'            => now(),
                            ]);
                    } else {
                        StudentBillPaymentBook::create([
                            'student_id'            => $request->student_id,
                            'school_bill_id'        => $schoolBillId,
                            'class_id'              => $request->class_id,
                            'term_id'               => $request->term_id,
                            'session_id'            => $request->session_id,
                            'amount_paid'           => $paymentForThisBill,
                            'amount_owed'           => max(0, $adjustedAmount - $paymentForThisBill),
                            'payment_status'        => $status,
                            'generated_by'          => $generatedBy,
                            'original_amount'       => $adjustedAmount,
                            'scholarship_deduction' => $scholarshipDeduction,
                            'discount_deduction'    => $discountDeduction,
                            'adjusted_amount'       => $adjustedAmount,
                        ]);
                    }
                } else {
                    $studentPayment = StudentBillPayment::create([
                        'student_id'     => $request->student_id,
                        'school_bill_id' => $schoolBillId,
                        'class_id'       => $request->class_id,
                        'termid_id'      => $request->term_id,
                        'session_id'     => $request->session_id,
                        'payment_method' => $request->payment_method,
                        'status'         => $status,
                        'generated_by'   => $generatedBy,
                        'delete_status'  => '1',
                    ]);

                    StudentBillPaymentRecord::create([
                        'student_bill_payment_id' => $studentPayment->id,
                        'class_id'                => $request->class_id,
                        'termid_id'               => $request->term_id,
                        'session_id'              => $request->session_id,
                        'amount_paid'             => $paymentForThisBill,
                        'last_payment'            => $paymentForThisBill,
                        'amount_owed'             => $newBalance,
                        'total_bill'              => $adjustedAmount,
                        'complete_payment'        => $completePayment,
                        'generated_by'            => $generatedBy,
                    ]);

                    StudentBillPaymentBook::create([
                        'student_id'            => $request->student_id,
                        'school_bill_id'        => $schoolBillId,
                        'class_id'              => $request->class_id,
                        'term_id'               => $request->term_id,
                        'session_id'            => $request->session_id,
                        'amount_paid'           => $paymentForThisBill,
                        'amount_owed'           => $newBalance,
                        'payment_status'        => $status,
                        'generated_by'          => $generatedBy,
                        'original_amount'       => $adjustedAmount,
                        'scholarship_deduction' => $scholarshipDeduction,
                        'discount_deduction'    => $discountDeduction,
                        'adjusted_amount'       => $adjustedAmount,
                    ]);
                }

                $paymentsProcessed[] = [
                    'bill_title'  => $billPayment['title'] ?? 'Bill',
                    'amount_paid' => $paymentForThisBill,
                    'balance'     => $newBalance,
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bulk payment recorded successfully.',
                'data'    => [
                    'payments_processed' => $paymentsProcessed,
                    'total_paid'         => $totalPaymentAmount - $remainingAmount,
                    'remaining_amount'   => $remainingAmount,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk payment store error: ', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to record payment: ' . $e->getMessage()], 500);
        }
    }

    // ── Delete payment record ─────────────────────────────────────────────

    public function deletestudentpayment($recordId)
    {
        try {
            DB::beginTransaction();

            $paymentRecord  = StudentBillPaymentRecord::findOrFail($recordId);
            $studentPayment = StudentBillPayment::findOrFail($paymentRecord->student_bill_payment_id);

            if ($studentPayment->delete_status == '0') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete payment record after invoice is generated.',
                ], 403);
            }

            $paymentBook = StudentBillPaymentBook::where([
                'student_id'     => $studentPayment->student_id,
                'school_bill_id' => $studentPayment->school_bill_id,
                'class_id'       => $studentPayment->class_id,
                'term_id'        => $studentPayment->termid_id,
                'session_id'     => $studentPayment->session_id,
            ])->first();

            if ($paymentBook) {
                // FIX #7: re-derive balances from the adjusted total rather than adding a stale delta.
                $adjustedTotal = (float) ($paymentBook->adjusted_amount ?: $paymentBook->original_amount);
                $newAmountPaid = max(0, (float) $paymentBook->amount_paid - (float) $paymentRecord->amount_paid);
                $newAmountOwed = max(0, $adjustedTotal - $newAmountPaid);

                DB::table('student_bill_payment_book')
                    ->where('id', $paymentBook->id)
                    ->update([
                        'amount_paid'    => $newAmountPaid,
                        'amount_owed'    => $newAmountOwed,
                        'payment_status' => $newAmountOwed <= 0 && $newAmountPaid > 0 ? 'Completed' : 'Pending',
                        'updated_at'     => now(),
                    ]);
            }

            $paymentRecord->delete();

            $remaining = StudentBillPaymentRecord::where('student_bill_payment_id', $studentPayment->id)->count();
            if ($remaining == 0) {
                $studentPayment->delete();
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment deleted successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete payment error: ', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()], 500);
        }
    }

    // ── Invoice ───────────────────────────────────────────────────────────

    public function invoice(Request $request, $studentId, $schoolclassid, $termid, $sessionid)
    {
        $pagetitle = 'Student Payment Invoice';

        $student = $this->fetchStudentData((int) $studentId, (int) $termid, (int) $sessionid);

        if (!$student) {
            return redirect()->route('payment.index')->with('error', 'Student not found or not enrolled.');
        }

        if (!$schoolclassid && $student->schoolclassId) {
            $schoolclassid = $student->schoolclassId;
        }

        if (!$schoolclassid) {
            return redirect()->route('payment.index')->with('error', 'Could not resolve student class for invoice.');
        }

        $safeAdmission = preg_replace('/[\/\\\\]/', '-', $student->admissionNo ?? $studentId);
        $invoiceNumber = 'INV-' . $safeAdmission . '-' . date('Ymd');

        try {
            $allClassBills = DB::table('school_bill_class_term_session')
                ->where('school_bill_class_term_session.class_id', $schoolclassid)
                ->where('school_bill_class_term_session.termid_id', $termid)
                ->where('school_bill_class_term_session.session_id', $sessionid)
                ->whereNull('school_bill_class_term_session.deleted_at')
                ->join('school_bill', 'school_bill.id', '=', 'school_bill_class_term_session.bill_id')
                ->where(function ($q) use ($student) {
                    $q->whereNull('school_bill.statusId')
                      ->orWhere('school_bill.statusId', '')
                      ->orWhere('school_bill.statusId', 0)
                      ->orWhere('school_bill.statusId', $student->statusId);
                })
                ->select([
                    'school_bill.id as school_bill_id',
                    'school_bill.title as title',
                    'school_bill.description as description',
                    'school_bill.bill_amount as amount',
                ])
                ->get();
        } catch (\Exception $e) {
            Log::error('Invoice bill query failed: ' . $e->getMessage());
            $allClassBills = collect();
        }

        $paidBills = StudentBillPayment::where('student_bill_payment.student_id', $studentId)
            ->where('student_bill_payment.class_id', $schoolclassid)
            ->where('student_bill_payment.termid_id', $termid)
            ->where('student_bill_payment.session_id', $sessionid)
            ->leftJoin('school_bill', 'school_bill.id', '=', 'student_bill_payment.school_bill_id')
            ->leftJoin('users', 'users.id', '=', 'student_bill_payment.generated_by')
            ->select([
                'student_bill_payment.id as paymentid',
                'student_bill_payment.school_bill_id',
                'student_bill_payment.created_at as payment_date',
                'student_bill_payment.payment_method as payment_method',
                'school_bill.title as title',
                'school_bill.description as description',
                'school_bill.bill_amount as amount',
                DB::raw('COALESCE(users.name, "Unknown") as receivedBy'),
            ])
            ->groupBy([
                'student_bill_payment.school_bill_id', 'student_bill_payment.id',
                'student_bill_payment.created_at', 'student_bill_payment.payment_method',
                'school_bill.title', 'school_bill.description', 'school_bill.bill_amount', 'users.name',
            ])
            ->get()
            ->keyBy('school_bill_id');

        // FIX #6: Fetch scholarship/discount once for the invoice map.
        $now = now();
        $scholarshipAssignment = ScholarshipAssignment::where('student_id', $studentId)
            ->where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
            })
            ->with('scholarship')
            ->first();

        $discountAssignments = DiscountAssignment::where('student_id', $studentId)
            ->where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
            })
            ->with('discount')
            ->get();

        $payments = $allClassBills->map(function ($bill) use ($paidBills, $studentId, $invoiceNumber, $scholarshipAssignment, $discountAssignments) {
            $adj      = $this->buildBillAdjustment(
                $studentId,
                $bill->school_bill_id,
                (float) $bill->amount,
                $scholarshipAssignment,
                $discountAssignments
            );
            $paidBill = $paidBills->get($bill->school_bill_id);

            if ($paidBill) {
                $paymentRecords       = StudentBillPaymentRecord::where('student_bill_payment_id', $paidBill->paymentid)->orderBy('created_at')->get();
                $totalPaidForThisBill = $paymentRecords->sum('amount_paid');
                $lastRecord           = $paymentRecords->sortByDesc('created_at')->first();
                $lastPaymentAmount    = $lastRecord ? $lastRecord->amount_paid : 0;
                $previousPaid         = $totalPaidForThisBill - $lastPaymentAmount;
                $currentBalance       = max(0, $adj['adjusted_amount'] - $totalPaidForThisBill);

                // FIX #10: invoiceNo is stamped only if not already set — but the
                // delete_status flip is now deferred to a separate explicit action
                // (confirmInvoice) so a page reload does NOT re-mark payments.
                if ($lastRecord && !$lastRecord->invoiceNo) {
                    StudentBillPaymentRecord::where('id', $lastRecord->id)
                        ->update(['invoiceNo' => $invoiceNumber]);
                }

                return (object) [
                    'school_bill_id'        => $bill->school_bill_id,
                    'title'                 => $bill->title,
                    'description'           => $bill->description,
                    'amount'                => $adj['adjusted_amount'],
                    'original_amount'       => $adj['original_amount'],
                    'scholarship_deduction' => $adj['scholarship_deduction'],
                    'discount_deduction'    => $adj['discount_deduction'],
                    'total_savings'         => $adj['total_savings'],
                    'previousPaid'          => $previousPaid,
                    'todayPaid'             => $lastPaymentAmount,
                    'amountPaid'            => $totalPaidForThisBill,
                    'balance'               => $currentBalance,
                    'paymentMethod'         => $paidBill->payment_method ?? 'N/A',
                    'receivedBy'            => $paidBill->receivedBy ?? 'Unknown',
                    'paymentDate'           => $lastRecord ? $lastRecord->created_at : $paidBill->payment_date,
                    'complete_payment'      => $currentBalance == 0 ? 1 : 0,
                    'invoiceNo'             => $lastRecord->invoiceNo ?? $invoiceNumber,
                ];
            }

            return (object) [
                'school_bill_id'        => $bill->school_bill_id,
                'title'                 => $bill->title,
                'description'           => $bill->description,
                'amount'                => $adj['adjusted_amount'],
                'original_amount'       => $adj['original_amount'],
                'scholarship_deduction' => $adj['scholarship_deduction'],
                'discount_deduction'    => $adj['discount_deduction'],
                'total_savings'         => $adj['total_savings'],
                'previousPaid'          => 0,
                'todayPaid'             => 0,
                'amountPaid'            => 0,
                'balance'               => $adj['adjusted_amount'],
                'paymentMethod'         => 'N/A',
                'receivedBy'            => 'N/A',
                'paymentDate'           => null,
                'complete_payment'      => 0,
                'invoiceNo'             => null,
            ];
        });

        $payments = $payments->groupBy('school_bill_id')
            ->map(fn($g) => $g->sortByDesc('paymentDate')->first())
            ->values()
            ->sortByDesc('paymentDate');

        $totalBillAmount   = $payments->sum('amount');
        $totalPreviousPaid = $payments->sum('previousPaid');
        $totalTodayPaid    = $payments->sum('todayPaid');
        $totalPaid         = $payments->sum('amountPaid');
        $totalOutstanding  = $payments->sum('balance');
        $totalSavings      = $payments->sum('total_savings');

        $schoolInfo    = SchoolInformation::first();
        $schoolterm    = optional(Schoolterm::find($termid))->term    ?? 'N/A';
        $schoolsession = optional(Schoolsession::find($sessionid))->session ?? 'N/A';

        // FIX #10: delete_status flip is NO LONGER done on GET (page load).
        // It must be triggered explicitly via confirmInvoice() POST route.
        // The invoice view should render a "Confirm & Finalise Invoice" button
        // that POSTs to that route, protecting against accidental re-loads.

        $fullName = $this->getFullNameWithOther($student->firstname, $student->lastname, $student->othername ?? '');

        $data = [
            'pagetitle'          => $pagetitle,
            'invoiceNumber'      => $invoiceNumber,
            'schoolterm'         => $schoolterm,
            'schoolsession'      => $schoolsession,
            'studentId'          => $studentId,
            'termid'             => $termid,
            'sessionid'          => $sessionid,
            'schoolclassid'      => $schoolclassid,
            'totalBillAmount'    => $totalBillAmount,
            'totalPreviousPaid'  => $totalPreviousPaid,
            'totalTodayPaid'     => $totalTodayPaid,
            'totalSavings'       => $totalSavings,
            'totalPaid'          => $totalPaid,
            'totalOutstanding'   => $totalOutstanding,
            'schoolInfo'         => $schoolInfo,
            'studentdata'        => $student ? collect([$student]) : collect([]),
            'studentpaymentbill' => $payments,
            'studentFullName'    => $fullName,
        ];

        if ($request->has('download_pdf')) {
            $safeFilename = 'invoice_' . preg_replace('/[\/\\\\]/', '-', $student->admissionNo ?? $studentId) . '.pdf';
            $pdf = PDF::loadView('schoolpayment.studentinvoicepdf', $data);
            return $pdf->download($safeFilename);
        }

        return view('schoolpayment.studentinvoice', $data);
    }

    /**
     * FIX #10: Explicit POST action to finalise / confirm an invoice.
     * This is the ONLY place that flips delete_status from 1 → 0,
     * so a simple page refresh of the invoice view can never do it accidentally.
     */
    public function confirmInvoice(Request $request, $studentId, $schoolclassid, $termid, $sessionid)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $updated = StudentBillPayment::where('student_id', $studentId)
            ->where('class_id', $schoolclassid)
            ->where('termid_id', $termid)
            ->where('session_id', $sessionid)
            ->where('delete_status', '1')
            ->update(['delete_status' => '0']);

        return response()->json([
            'success' => true,
            'message' => 'Invoice confirmed. ' . $updated . ' payment record(s) finalised.',
        ]);
    }

    // ── Statement ─────────────────────────────────────────────────────────

    public function statement(Request $request, $studentId, $schoolclassid, $termid, $sessionid)
    {
        $pagetitle = 'Student Payment Statement';

        $student = $this->fetchStudentData((int) $studentId, (int) $termid, (int) $sessionid);

        if (!$student) {
            return redirect()->route('payment.index')->with('error', 'Student not found.');
        }

        // FIX #5: use class/term/session-specific bills, not all bills in the system.
        $resolvedClassId = $schoolclassid ?: $student->schoolclassId;

        $student_bill_info = DB::table('school_bill_class_term_session')
            ->where('school_bill_class_term_session.class_id', $resolvedClassId)
            ->where('school_bill_class_term_session.termid_id', $termid)
            ->where('school_bill_class_term_session.session_id', $sessionid)
            ->whereNull('school_bill_class_term_session.deleted_at')
            ->join('school_bill', 'school_bill.id', '=', 'school_bill_class_term_session.bill_id')
            ->where(function ($q) use ($student) {
                $q->whereNull('school_bill.statusId')
                  ->orWhere('school_bill.statusId', '')
                  ->orWhere('school_bill.statusId', 0)
                  ->orWhere('school_bill.statusId', $student->statusId);
            })
            ->select([
                DB::raw('school_bill.id as schoolbillid'),
                'school_bill.title',
                'school_bill.description',
                DB::raw('school_bill.bill_amount as amount'),
            ])
            ->get();

        $studentpaymentbillbook = StudentBillPaymentBook::where('student_id', $studentId)
            ->where('term_id', $termid)
            ->where('session_id', $sessionid)
            ->get();

        $studentpaymentbill = StudentBillPayment::where('student_bill_payment.student_id', $studentId)
            ->where('student_bill_payment.class_id', $resolvedClassId)
            ->where('student_bill_payment.termid_id', $termid)
            ->where('student_bill_payment.session_id', $sessionid)
            ->leftJoin('student_bill_payment_record', 'student_bill_payment_record.student_bill_payment_id', '=', 'student_bill_payment.id')
            ->leftJoin('school_bill', 'school_bill.id', '=', 'student_bill_payment.school_bill_id')
            ->leftJoin('users', 'users.id', '=', 'student_bill_payment.generated_by')
            ->select([
                'student_bill_payment_record.created_at as payment_date',
                'student_bill_payment.payment_method as payment_method',
                'school_bill.title as title',
                'school_bill.description as description',
                'school_bill.bill_amount as amount',
                'student_bill_payment_record.amount_paid as amount_paid',
                'student_bill_payment_record.amount_owed as balance',
                DB::raw('CASE WHEN student_bill_payment_record.complete_payment = 1 THEN "Completed" ELSE "Pending" END as payment_status'),
                DB::raw('COALESCE(users.name, "Unknown") as received_by'),
            ])
            ->orderBy('student_bill_payment_record.created_at', 'desc')
            ->get();

        // FIX #5: totals now reflect only this student's class bills for the term/session.
        $now = now();
        $scholarshipAssignment = ScholarshipAssignment::where('student_id', $studentId)
            ->where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
            })
            ->with('scholarship')
            ->first();

        $discountAssignments = DiscountAssignment::where('student_id', $studentId)
            ->where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
            })
            ->with('discount')
            ->get();

        $totalSchoolBill = $student_bill_info->sum(function ($bill) use ($studentId, $scholarshipAssignment, $discountAssignments) {
            $adj = $this->buildBillAdjustment(
                $studentId,
                $bill->schoolbillid,
                (float) $bill->amount,
                $scholarshipAssignment,
                $discountAssignments
            );
            return $adj['adjusted_amount'];
        });

        $totalPaid        = $studentpaymentbillbook->sum('amount_paid');
        $totalOutstanding = max(0, $totalSchoolBill - $totalPaid);
        $schoolInfo       = SchoolInformation::first();

        $safeAdmission   = preg_replace('/[\/\\\\]/', '-', $student->admissionNo ?? $studentId);
        $statementNumber = 'STMT-' . $safeAdmission . '-' . date('Ymd');

        $schoolterm    = optional(Schoolterm::find($termid))->term    ?? 'N/A';
        $schoolsession = optional(Schoolsession::find($sessionid))->session ?? 'N/A';

        $fullName = $this->getFullNameWithOther($student->firstname, $student->lastname, $student->othername ?? '');

        $data = [
            'pagetitle'          => $pagetitle,
            'studentpaymentbill' => $studentpaymentbill,
            'totalSchoolBill'    => $totalSchoolBill,
            'totalPaid'          => $totalPaid,
            'totalOutstanding'   => $totalOutstanding,
            'schoolInfo'         => $schoolInfo,
            'statementNumber'    => $statementNumber,
            'schoolterm'         => $schoolterm,
            'schoolsession'      => $schoolsession,
            'studentId'          => $studentId,
            'termid'             => $termid,
            'sessionid'          => $sessionid,
            'studentdata'        => $student ? collect([$student]) : collect([]),
            'studentFullName'    => $fullName,
        ];

        $safeFilename = 'statement_' . $safeAdmission . '.pdf';
        $pdf = PDF::loadView('schoolpayment.studentstatement', $data);
        return $pdf->download($safeFilename);
    }
}
