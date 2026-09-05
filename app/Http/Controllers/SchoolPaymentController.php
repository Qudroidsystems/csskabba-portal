<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\DiscountAssignment;
use App\Models\Scholarship;
use App\Models\ScholarshipAssignment;
use App\Models\SchoolBillModel;
use App\Models\SchoolBillTermSession;
use App\Models\Schoolclass;
use App\Models\SchoolInformation;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Student;
use App\Models\StudentBillPayment;
use App\Models\StudentBillPaymentBook;
use App\Models\StudentBillPaymentRecord;
use App\Models\Studentpicture;
use App\Services\Billing\BillAdjustmentService;
use App\Services\Billing\PaymentAuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class SchoolPaymentController extends Controller
{
    protected BillAdjustmentService $billAdjustment;
    protected PaymentAuditService $audit;

    public function __construct(BillAdjustmentService $billAdjustment, PaymentAuditService $audit)
    {
        $this->billAdjustment = $billAdjustment;
        $this->audit          = $audit;
    }

    /**
     * Display student list for payment selection (initial page shell only;
     * the table itself is populated by data() via server-side DataTables).
     */
    public function index(Request $request)
    {
        $pagetitle      = 'Student Payments';
        $schoolterms    = Schoolterm::all();
        $schoolsessions = Schoolsession::all();

        $classOptions = Schoolclass::leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->select(['schoolclass.schoolclass as schoolclass'])
            ->distinct()
            ->orderBy('schoolclass')
            ->pluck('schoolclass')
            ->filter()
            ->values();

        $statusOptions = Student::whereNotNull('student_status')
            ->where('student_status', '!=', '')
            ->distinct()
            ->orderBy('student_status')
            ->pluck('student_status');

        return view('schoolpayment.index', compact(
            'pagetitle', 'schoolterms', 'schoolsessions', 'classOptions', 'statusOptions'
        ));
    }

    /**
     * Stat card counts for the student payments index (AJAX).
     */
    public function stats()
    {
        $now = now();

        $base = Student::leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
            ->leftJoin('schoolsession', 'schoolsession.id', '=', 'studentclass.sessionid')
            ->where('schoolsession.status', 'Current');

        $total  = (clone $base)->count('studentRegistration.id');
        $active = (clone $base)->where('studentRegistration.student_status', 'Active')->count('studentRegistration.id');

        $studentIds = (clone $base)->pluck('studentRegistration.id');

        $scholarshipCount = ScholarshipAssignment::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
            })
            ->distinct('student_id')
            ->count('student_id');

        $discountCount = DiscountAssignment::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
            })
            ->distinct('student_id')
            ->count('student_id');

        return response()->json([
            'stats' => [
                'total'       => $total,
                'active'      => $active,
                'scholarship' => $scholarshipCount,
                'discount'    => $discountCount,
            ],
        ]);
    }

    /**
     * Server-side DataTables endpoint for the student payments index.
     */
    public function data(Request $request)
    {
        $now = now();

        $query = Student::leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
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
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as arm',
                'schoolterm.term as term',
                'schoolsession.session as session',
                'studentpicture.picture as picture',
            ]);

        $scholarshipIds = ScholarshipAssignment::where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
            })
            ->pluck('student_id')
            ->flip();

        $discountIds = DiscountAssignment::where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now);
            })
            ->pluck('student_id')
            ->flip();

        return DataTables::of($query)
            ->filter(function ($q) use ($request) {
                if ($search = $request->input('search.value')) {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('studentRegistration.firstname', 'like', "%{$search}%")
                            ->orWhere('studentRegistration.lastname', 'like', "%{$search}%")
                            ->orWhere('studentRegistration.admissionNo', 'like', "%{$search}%")
                            ->orWhere('schoolclass.schoolclass', 'like', "%{$search}%");
                    });
                }
                if ($class = $request->input('class_filter')) {
                    $q->where('schoolclass.schoolclass', $class);
                }
                if ($status = $request->input('status_filter')) {
                    $q->where('studentRegistration.student_status', $status);
                }
            })
            ->addColumn('avatar', function ($s) {
                $initials = strtoupper(substr($s->firstname ?? '', 0, 1) . substr($s->lastname ?? '', 0, 1)) ?: 'ST';
                if ($s->picture && $s->picture !== 'unnamed.jpg' && $s->picture !== '') {
                    $url = asset('storage/images/student_avatars/' . $s->picture);
                    return '<div class="p-avatar"><img src="' . $url . '" alt="" onerror="this.remove()"></div>';
                }
                return '<div class="p-avatar">' . e($initials) . '</div>';
            })
            ->addColumn('full_name', function ($s) {
                $name = trim(($s->firstname ?? '') . ' ' . ($s->lastname ?? ''));
                return '<div class="fw-semibold" style="color:var(--p-primary)">' . e($name) . '</div>'
                    . '<div class="text-muted small">' . e($s->gender) . '</div>';
            })
            ->addColumn('class_display', function ($s) {
                $val = trim(($s->schoolclass ?? '') . ' ' . ($s->arm ?? ''));
                return $val !== '' ? e($val) : '—';
            })
            ->addColumn('term_session_display', function ($s) {
                return e(($s->term ?: '—') . ' · ' . ($s->session ?: '—'));
            })
            ->addColumn('status_badge', function ($s) {
                if (!$s->student_status) {
                    return '<span class="p-pill status-default"><i class="bi bi-dash"></i>Unknown</span>';
                }
                $cls = strtolower($s->student_status) === 'active' ? 'status-active' : 'status-inactive';
                return '<span class="p-pill ' . $cls . '"><i class="bi bi-circle-fill" style="font-size:6px"></i>' . e($s->student_status) . '</span>';
            })
            ->addColumn('adjustments', function ($s) use ($scholarshipIds, $discountIds) {
                $out = '';
                if (isset($scholarshipIds[$s->id])) {
                    $out .= '<span class="p-pill scholarship"><i class="bi bi-award-fill"></i>Scholarship</span> ';
                }
                if (isset($discountIds[$s->id])) {
                    $out .= '<span class="p-pill discount"><i class="bi bi-tag-fill"></i>Discount</span>';
                }
                return $out !== '' ? $out : '<span class="p-pill none"><i class="bi bi-dash"></i>None</span>';
            })
            ->addColumn('action', function ($s) {
                $name = trim(($s->firstname ?? '') . ' ' . ($s->lastname ?? ''));
                return '<button type="button" class="p-action-btn pay select-term-session-btn" '
                    . 'data-student-id="' . $s->id . '" data-student-name="' . e($name) . '" title="Manage Payment">'
                    . '<i class="bi bi-cash-coin"></i></button>';
            })
            ->rawColumns(['avatar', 'full_name', 'class_display', 'term_session_display', 'status_badge', 'adjustments', 'action'])
            ->make(true);
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
     * Term/Session selector page (legacy fallback — the index now uses a modal instead)
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

    // ── Fetch student data helper ─────────────────────────────────────────

    /**
     * Resolve the student's enrollment for a specific term/session.
     *
     * FIX: the previous version joined `studentclass` on studentId only, with
     * no term/session filter, so a student with more than one studentclass
     * row (e.g. after being promoted to a new class) could have `->first()`
     * return the wrong enrollment. That mismatched class_id then silently
     * produced an empty/incorrect bills list — which is why "amount paid"
     * and "progress" appeared to stop showing.
     *
     * This now looks for the exact term/session enrollment first. If none
     * exists, it falls back to the student's most recent enrollment and
     * flags `used_fallback = true` on the returned object so the caller can
     * warn the UI (the studentpayment.blade.php view already has a banner
     * wired up for this — it just never received the flag before).
     */
    private function fetchStudentData(int $studentId, int $termid, int $sessionid): ?object
    {
        $buildBase = function () use ($studentId) {
            return Student::where('studentRegistration.id', $studentId)
                ->leftJoin('parentRegistration', 'parentRegistration.id', '=', 'studentRegistration.id')
                ->leftJoin('studentpicture', 'studentpicture.studentid', '=', 'studentRegistration.id')
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
                ]);
        };

        // 1) Prefer the enrollment that matches this exact term/session.
        $exact = $buildBase()
            ->leftJoin('studentclass', function ($join) use ($termid, $sessionid) {
                $join->on('studentclass.studentId', '=', 'studentRegistration.id')
                    ->where('studentclass.termid', $termid)
                    ->where('studentclass.sessionid', $sessionid);
            })
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->addSelect([
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as arm',
                'studentclass.schoolclassid as schoolclassId',
            ])
            ->first();

        if ($exact && $exact->schoolclassId) {
            $exact->used_fallback = false;
            return $exact;
        }

        // 2) No enrollment record for this exact term/session (e.g. the
        // student was promoted/moved since) — fall back to the most recent
        // enrollment so the page still shows something, flagged accordingly.
        $fallback = $buildBase()
            ->leftJoin('studentclass', 'studentclass.studentId', '=', 'studentRegistration.id')
            ->leftJoin('schoolclass', 'schoolclass.id', '=', 'studentclass.schoolclassid')
            ->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->addSelect([
                'schoolclass.schoolclass as schoolclass',
                'schoolarm.arm as arm',
                'studentclass.schoolclassid as schoolclassId',
            ])
            ->orderByDesc('studentclass.id')
            ->first();

        if ($fallback) {
            $fallback->used_fallback = true;
        }

        return $fallback;
    }

    /**
     * Helper method to prepare school info with base64 images for PDF
     */
    private function prepareSchoolInfoForPdf($schoolInfo)
    {
        if (!$schoolInfo) {
            return null;
        }

        // Get logo base64
        if ($schoolInfo->school_logo && Storage::disk('public')->exists($schoolInfo->school_logo)) {
            $path = Storage::disk('public')->path($schoolInfo->school_logo);
            $mime = mime_content_type($path) ?: 'image/png';
            $data = base64_encode(file_get_contents($path));
            $schoolInfo->logo_base64 = "data:{$mime};base64,{$data}";
        } else {
            $schoolInfo->logo_base64 = null;
        }

        // Get stamp base64
        if ($schoolInfo->school_stamp && Storage::disk('public')->exists($schoolInfo->school_stamp)) {
            $path = Storage::disk('public')->path($schoolInfo->school_stamp);
            $mime = mime_content_type($path) ?: 'image/png';
            $data = base64_encode(file_get_contents($path));
            $schoolInfo->stamp_base64 = "data:{$mime};base64,{$data}";
        } else {
            $schoolInfo->stamp_base64 = null;
        }

        // Format phones if needed
        if (empty($schoolInfo->formatted_phones) && !empty($schoolInfo->school_phones)) {
            $schoolInfo->formatted_phones = implode(', ', $schoolInfo->school_phones);
        }

        return $schoolInfo;
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
            $usedFallback  = (bool) ($studentdata->used_fallback ?? false);

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
                $adj = $this->billAdjustment->buildBillAdjustment(
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

                // FIX: when a bill is fully waived by scholarship/discount
                // (adjusted amount = 0), it should read as 100% / paid even
                // though nothing was ever actually paid — previously it fell
                // through to 0% and "not paid".
                $progress = $adjustedAmt > 0
                    ? min(100, ($amountPaid / $adjustedAmt) * 100)
                    : ($balance <= 0 ? 100 : 0);

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
                    // FIX: a bill with balance <= 0 is paid, even if
                    // amount_paid happens to be 0 (fully waived bill).
                    'is_paid'               => $balance <= 0,
                    'is_partial'            => $amountPaid > 0 && $balance > 0,
                ];
            }

            // Payment records (pending / not yet invoiced) — one row per
            // bill, aggregated across all payments made on it so far.
            //
            // FIX: previously this joined only the single latest payment
            // record and displayed its own amount_paid as "totalAmountPaid",
            // which under-reported the true paid amount whenever a bill had
            // more than one pending payment before an invoice was generated.
            $paymentRecords = StudentBillPayment::where('student_bill_payment.student_id', $studentId)
                ->where('student_bill_payment.termid_id', $termid)
                ->where('student_bill_payment.session_id', $sessionid)
                ->where('student_bill_payment.delete_status', '1')
                ->leftJoin('school_bill', 'school_bill.id', '=', 'student_bill_payment.school_bill_id')
                ->leftJoin('users', 'users.id', '=', 'student_bill_payment.generated_by')
                ->select([
                    'student_bill_payment.id as paymentId',
                    'student_bill_payment.school_bill_id as school_bill_id',
                    'student_bill_payment.payment_method as paymentMethod',
                    'student_bill_payment.status as paymentStatus',
                    'school_bill.title as title',
                    'school_bill.description as description',
                    'school_bill.bill_amount as billAmount',
                    DB::raw('(SELECT MAX(spr.id) FROM student_bill_payment_record spr WHERE spr.student_bill_payment_id = student_bill_payment.id) as recordId'),
                    DB::raw('(SELECT MAX(spr.created_at) FROM student_bill_payment_record spr WHERE spr.student_bill_payment_id = student_bill_payment.id) as receivedDate'),
                    DB::raw('(SELECT COALESCE(SUM(spr.amount_paid), 0) FROM student_bill_payment_record spr WHERE spr.student_bill_payment_id = student_bill_payment.id) as totalAmountPaid'),
                    DB::raw('(SELECT spr.amount_owed FROM student_bill_payment_record spr WHERE spr.student_bill_payment_id = student_bill_payment.id ORDER BY spr.id DESC LIMIT 1) as balance'),
                    DB::raw('COALESCE(users.name, "Unknown") as receivedBy'),
                ])
                ->get();

            // Payment history (invoiced / completed) — one row per
            // transaction, kept as-is: this is a transaction log rather than
            // a per-bill summary, so multiple rows per bill is intended.
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
                'success'       => true,
                'used_fallback' => $usedFallback,
                'data'    => [
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
                // Kept at top level as well since result.used_fallback is what
                // the blade view reads.
                'used_fallback' => $usedFallback,
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

            $bill = SchoolBillModel::findOrFail($request->school_bill_id);

            // ALWAYS recompute server-side — never trust client-submitted
            // adjusted_amount / scholarship_deduction / discount_deduction.
            $adj = $this->billAdjustment->buildBillAdjustment(
                (int) $request->student_id,
                (int) $request->school_bill_id,
                (float) $bill->bill_amount
            );
            $finalAdjustedAmount = $adj['adjusted_amount'];

            if ($studentPayment) {
                $records   = StudentBillPaymentRecord::where('student_bill_payment_id', $studentPayment->id)->get();
                $totalPaid = $records->sum('amount_paid');
                if ($totalPaid >= $finalAdjustedAmount) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'This bill is already fully paid.'], 422);
                }
            }

            $paymentAmount  = (float) $request->payment_amount;

            $alreadyPaid = StudentBillPaymentBook::where([
                'student_id' => $request->student_id, 'school_bill_id' => $request->school_bill_id,
                'class_id'   => $request->class_id, 'term_id' => $request->term_id, 'session_id' => $request->session_id,
            ])->value('amount_paid') ?? 0;

            $currentBalance = max(0, $finalAdjustedAmount - $alreadyPaid);

            if ($paymentAmount > $currentBalance + 0.01) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Payment amount exceeds the actual outstanding balance.'], 422);
            }

            $balance         = $currentBalance - $paymentAmount;
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

            $this->audit->log('recorded', [
                'student_id'               => $request->student_id,
                'school_bill_id'           => $request->school_bill_id,
                'student_bill_payment_id'  => $studentPayment->id,
                'class_id'                 => $request->class_id,
                'term_id'                  => $request->term_id,
                'session_id'               => $request->session_id,
                'amount'                   => $paymentAmount,
                'payment_method'           => $request->payment_method2,
                'entity_type'              => 'payment',
            ], null, [
                'amount'                => $paymentAmount,
                'adjusted_amount'       => $finalAdjustedAmount,
                'scholarship_deduction' => $adj['scholarship_deduction'],
                'discount_deduction'    => $adj['discount_deduction'],
                'new_balance'           => $balance,
            ], 'Individual payment recorded');

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
                'student_id'                     => 'required|integer|exists:studentRegistration,id',
                'class_id'                       => 'required|integer|exists:schoolclass,id',
                'term_id'                        => 'required|integer|exists:schoolterm,id',
                'session_id'                     => 'required|integer|exists:schoolsession,id',
                'payment_amount'                 => 'required|numeric|min:0.01',
                'payment_method'                 => 'required|string|in:Bank Deposit,School POS,Bank Transfer,Cheque',
                'bill_payments'                  => 'required|array|min:1',
                'bill_payments.*.school_bill_id' => 'required|integer|exists:school_bill,id',
                'bill_payments.*.title'          => 'nullable|string|max:255',
                // adjusted_amount / balance / scholarship_deduction / discount_deduction from the
                // client are IGNORED below — kept only so old front-end payloads don't fail validation.
                'bill_payments.*.adjusted_amount'       => 'nullable|numeric|min:0',
                'bill_payments.*.balance'               => 'nullable|numeric|min:0',
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

                $schoolBillId = (int) $billPayment['school_bill_id'];
                $bill         = SchoolBillModel::findOrFail($schoolBillId);

                // SERVER-SIDE RECOMPUTE — the critical fix. Never trust the
                // adjusted_amount / scholarship_deduction / discount_deduction
                // that the browser sent for this bill.
                $adj = $this->billAdjustment->buildBillAdjustment(
                    (int) $request->student_id,
                    $schoolBillId,
                    (float) $bill->bill_amount
                );
                $adjustedAmount       = $adj['adjusted_amount'];
                $scholarshipDeduction = $adj['scholarship_deduction'];
                $discountDeduction    = $adj['discount_deduction'];

                $existingBook = StudentBillPaymentBook::where([
                    'student_id'     => $request->student_id,
                    'school_bill_id' => $schoolBillId,
                    'class_id'       => $request->class_id,
                    'term_id'        => $request->term_id,
                    'session_id'     => $request->session_id,
                ])->first();

                $alreadyPaid    = $existingBook ? (float) $existingBook->amount_paid : 0.0;
                $currentBalance = max(0, $adjustedAmount - $alreadyPaid);

                if ($currentBalance <= 0) continue;

                $paymentForThisBill = min($remainingAmount, $currentBalance);
                if ($paymentForThisBill <= 0) continue;

                $remainingAmount -= $paymentForThisBill;
                $newTotalPaid     = $alreadyPaid + $paymentForThisBill;
                $newBalance       = max(0, $adjustedAmount - $newTotalPaid);
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
                }

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

                if ($existingBook) {
                    DB::table('student_bill_payment_book')
                        ->where('id', $existingBook->id)
                        ->update([
                            'amount_paid'           => $newTotalPaid,
                            'amount_owed'           => $newBalance,
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
                        'amount_owed'           => $newBalance,
                        'payment_status'        => $status,
                        'generated_by'          => $generatedBy,
                        'original_amount'       => $bill->bill_amount,
                        'scholarship_deduction' => $scholarshipDeduction,
                        'discount_deduction'    => $discountDeduction,
                        'adjusted_amount'       => $adjustedAmount,
                    ]);
                }

                $paymentsProcessed[] = [
                    'school_bill_id'        => $schoolBillId,
                    'bill_title'            => $billPayment['title'] ?? $bill->title,
                    'amount_paid'           => $paymentForThisBill,
                    'balance'               => $newBalance,
                    'scholarship_deduction' => $scholarshipDeduction,
                    'discount_deduction'    => $discountDeduction,
                ];
            }

            DB::commit();

            $this->audit->log('bulk_recorded', [
                'student_id'     => $request->student_id,
                'class_id'       => $request->class_id,
                'term_id'        => $request->term_id,
                'session_id'     => $request->session_id,
                'amount'         => $totalPaymentAmount - $remainingAmount,
                'payment_method' => $request->payment_method,
                'entity_type'    => 'bulk_payment',
            ], null, ['bills' => $paymentsProcessed], 'Bulk payment recorded (server-recomputed adjustments)');

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

    /**
     * FIX: this previously only subtracted the deleted record's amount from
     * the payment book, and never touched:
     *   - the amount_owed / complete_payment stored on the record(s) left
     *     behind (which the Payment Records / History tables read directly)
     *   - the parent student_bill_payment.status
     *
     * So deleting anything other than the very last payment on a bill left
     * stale balances on-screen, and a bill that becomes newly "incomplete"
     * after a deletion could still show as Completed.
     *
     * This now recomputes amount_paid / amount_owed / completion state from
     * the remaining records (source of truth) rather than subtracting, and
     * keeps every dependent row (book, last remaining record, parent
     * payment status) in sync with that recomputed truth.
     */
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

            $oldValues = [
                'amount_paid' => $paymentRecord->amount_paid,
                'amount_owed' => $paymentRecord->amount_owed,
            ];

            $paymentBook = StudentBillPaymentBook::where([
                'student_id'     => $studentPayment->student_id,
                'school_bill_id' => $studentPayment->school_bill_id,
                'class_id'       => $studentPayment->class_id,
                'term_id'        => $studentPayment->termid_id,
                'session_id'     => $studentPayment->session_id,
            ])->first();

            $paymentRecord->delete();

            // Recompute from the records that remain (source of truth),
            // instead of subtracting — correct regardless of whether the
            // first, a middle, or the last payment was the one removed.
            $remainingRecords = StudentBillPaymentRecord::where('student_bill_payment_id', $studentPayment->id)
                ->orderBy('created_at')
                ->get();

            $adjustedTotal = $paymentBook
                ? (float) ($paymentBook->adjusted_amount ?: $paymentBook->original_amount)
                : max(0, (float) $paymentRecord->total_bill);

            $newAmountPaid = $remainingRecords->sum('amount_paid');
            $newAmountOwed = max(0, $adjustedTotal - $newAmountPaid);
            $isComplete    = $newAmountPaid > 0 && $newAmountOwed <= 0;

            if ($paymentBook) {
                DB::table('student_bill_payment_book')
                    ->where('id', $paymentBook->id)
                    ->update([
                        'amount_paid'    => $newAmountPaid,
                        'amount_owed'    => $newAmountOwed,
                        'payment_status' => $isComplete ? 'Completed' : 'Pending',
                        'updated_at'     => now(),
                    ]);
            }

            if ($remainingRecords->isEmpty()) {
                // Nothing left for this bill — remove the parent payment row
                // too, so it stops appearing as a pending/completed payment.
                $studentPayment->delete();
            } else {
                // Keep the most recent remaining record's stored balance and
                // completion flag in sync, since the Payment Records /
                // History tables read amount_owed / complete_payment
                // straight off the record rather than recomputing them.
                $lastRecord = $remainingRecords->last();
                StudentBillPaymentRecord::where('id', $lastRecord->id)->update([
                    'amount_owed'      => $newAmountOwed,
                    'total_bill'       => $adjustedTotal,
                    'complete_payment' => $isComplete ? 1 : 0,
                ]);

                DB::table('student_bill_payment')
                    ->where('id', $studentPayment->id)
                    ->update([
                        'status'     => $isComplete ? 'Completed' : 'Pending',
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            $this->audit->log('deleted', [
                'student_id'                     => $studentPayment->student_id,
                'school_bill_id'                 => $studentPayment->school_bill_id,
                'student_bill_payment_id'        => $studentPayment->id,
                'student_bill_payment_record_id' => $recordId,
                'class_id'                       => $studentPayment->class_id,
                'term_id'                        => $studentPayment->termid_id,
                'session_id'                      => $studentPayment->session_id,
                'amount'                         => $oldValues['amount_paid'],
                'entity_type'                    => 'payment',
            ], $oldValues, [
                'amount_paid' => $newAmountPaid,
                'amount_owed' => $newAmountOwed,
            ], 'Payment record deleted');

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
        
        // ================================================================
        // INVOICE NUMBER GENERATION - UNIQUE WITH TIMESTAMP
        // ================================================================
        // Format: INV-[ADMISSION]-[YYYYMMDDHHMMSS]-[UNIQUE_ID]
        // Example: INV-STU123-20260304143215-A7B3
        // ================================================================
        $timestamp = date('YmdHis'); // YearMonthDayHourMinuteSecond
        $uniqueId = strtoupper(substr(uniqid(), -4)); // Last 4 chars of unique ID
        $invoiceNumber = 'INV-' . $safeAdmission . '-' . $timestamp . '-' . $uniqueId;

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
            $adj      = $this->billAdjustment->buildBillAdjustment(
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

        // Get school info and prepare base64 for PDF
        $schoolInfo = SchoolInformation::first();
        $schoolInfo = $this->prepareSchoolInfoForPdf($schoolInfo);

        $schoolterm    = optional(Schoolterm::find($termid))->term    ?? 'N/A';
        $schoolsession = optional(Schoolsession::find($sessionid))->session ?? 'N/A';

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
            // Downloading the invoice is treated as generating/finalizing it:
            // move any pending payments for this student/class/term/session
            // out of "Payment Records" and into "History".
            StudentBillPayment::where('student_id', $studentId)
                ->where('class_id', $schoolclassid)
                ->where('termid_id', $termid)
                ->where('session_id', $sessionid)
                ->where('delete_status', '1')
                ->update(['delete_status' => '0']);

            $this->audit->log('invoice_confirmed', [
                'student_id' => $studentId, 'class_id' => $schoolclassid,
                'term_id'    => $termid,    'session_id' => $sessionid,
                'entity_type'=> 'invoice',
            ], null, ['invoice_number' => $invoiceNumber], 'Invoice generated via PDF download');

            // Unique filename with timestamp
            $safeFilename = 'invoice_' . preg_replace('/[\/\\\\]/', '-', $student->admissionNo ?? $studentId) . '_' . date('Ymd_His') . '.pdf';
            
            $pdf = PDF::loadView('schoolpayment.studentinvoicepdf', $data)
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => false,
                ]);
            return $pdf->download($safeFilename);
        }

        return view('schoolpayment.studentinvoice', $data);
    }

    /**
     * Explicit POST action to finalise / confirm an invoice from the web view
     * (moves delete_status 1 → 0 for this student/class/term/session). This
     * is the only other place — besides the PDF download above — that flips
     * this flag, so a page refresh of the invoice view alone can never do it.
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

        $this->audit->log('invoice_confirmed', [
            'student_id' => $studentId, 'class_id' => $schoolclassid,
            'term_id'    => $termid,    'session_id' => $sessionid,
            'entity_type'=> 'invoice',
        ], null, ['records_finalized' => $updated], 'Invoice confirmed from web view');

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
            $adj = $this->billAdjustment->buildBillAdjustment(
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

        // Get school info and prepare base64 for PDF
        $schoolInfo = SchoolInformation::first();
        $schoolInfo = $this->prepareSchoolInfoForPdf($schoolInfo);

        $safeAdmission   = preg_replace('/[\/\\\\]/', '-', $student->admissionNo ?? $studentId);
        $statementNumber = 'STMT-' . $safeAdmission . '-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));

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

        $safeFilename = 'statement_' . $safeAdmission . '_' . date('Ymd_His') . '.pdf';
        $pdf = PDF::loadView('schoolpayment.studentstatement', $data)
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false,
            ]);
        return $pdf->download($safeFilename);
    }
}