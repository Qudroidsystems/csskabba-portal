<?php

namespace App\Http\Controllers;

use App\Models\DiscountAssignment;
use App\Models\ScholarshipAssignment;
use App\Models\SchoolInformation;
use App\Models\Schoolsession;
use App\Models\Schoolterm;
use App\Models\Student;
use App\Services\Billing\ArrearsService;
use App\Services\Billing\BillAdjustmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Student-facing "My Payments" portal.
 *
 * Every figure here is computed the SAME way as the bursar's payment screen
 * (SchoolPaymentController::getPaymentDetailsAjax), so what a student sees
 * always matches what the school sees:
 *
 *  - Bills: school_bill_class_term_session for the class/term/session,
 *    excluding soft-deleted assignments and bills meant for a different
 *    student status (e.g. boarding-only bills for a day student).
 *  - Payable amount: BillAdjustmentService::buildBillAdjustment() -- the one
 *    canonical scholarship + discount calculation (stacking, priority,
 *    caps, sibling discounts).
 *  - Amount paid: SUM of the payment LEDGER (student_bill_payment_record),
 *    excluding soft-deleted rows -- not the running total_paid counter, and
 *    not just the first payment row.
 *  - History: one line per ledger transaction, not per bill summary.
 *  - Arrears: ArrearsService, i.e. unpaid balances from other terms.
 *
 * The student's class for a term is resolved from their enrolment, payment
 * book, payment rows or results -- studentclass only holds the CURRENT
 * placement, so on its own it made every past session show "no bills".
 */
class StudentPaymentController extends Controller
{
    public function __construct(
        protected BillAdjustmentService $billAdjustment,
        protected ArrearsService $arrears
    ) {
        $this->middleware('permission:View student payments', ['only' => ['index', 'printReceipt']]);
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request)
    {
        $pagetitle = 'My Payments';
        $studentId = (int) auth()->user()->student_id;

        $student = $studentId
            ? Student::where('id', $studentId)
                ->select('id', 'firstname', 'lastname', 'othername', 'admissionNo', 'gender', 'statusId')
                ->first()
            : null;

        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student profile not found.');
        }

        [$sessions, $terms] = $this->periodOptions($studentId);
        [$sessionId, $termId] = $this->resolvePeriod($request, $studentId, $sessions);

        $statement = $this->buildStatement($student, $termId, $sessionId);

        $studentPicture = DB::table('studentpicture')->where('studentid', $studentId)->orderByDesc('id')->value('picture');

        return view('student.payments.index', array_merge($statement, [
            'pagetitle'         => $pagetitle,
            'student'           => $student,
            'terms'             => $terms,
            'sessions'          => $sessions,
            'selectedSessionId' => $sessionId,
            'selectedTermId'    => $termId,
            'studentPicture'    => $studentPicture,
            'schoolInfo'        => SchoolInformation::first(),
            'paymentTrend'      => $this->buildPaymentTrend($studentId, $sessionId),
        ]));
    }

    // =========================================================================
    // RECEIPT / STATEMENT PDF
    // =========================================================================

    public function printReceipt(Request $request)
    {
        ini_set('max_execution_time', 120);
        ini_set('memory_limit', '512M');

        $studentId = (int) auth()->user()->student_id;
        $student   = $studentId ? Student::find($studentId) : null;

        if (!$student) {
            return back()->with('error', 'Student profile not found.');
        }

        [$sessions] = $this->periodOptions($studentId);
        [$sessionId, $termId] = $this->resolvePeriod($request, $studentId, $sessions);

        $statement = $this->buildStatement($student, $termId, $sessionId);

        if (!$statement['class']) {
            return back()->with('error', 'No class or fee record found for the selected term and session.');
        }

        $schoolInfo  = SchoolInformation::first();
        $termName    = $statement['term']->term ?? 'Term';
        $sessionName = $statement['session']->session ?? 'Session';

        $filename  = 'Payment_Statement_' . Str::slug($student->admissionNo ?? 'student', '-') . '_' . Str::slug($termName, '-') . '.pdf';
        $receiptNo = 'RCP-' . strtoupper(substr(md5($studentId . '-' . $termId . '-' . $sessionId), 0, 8));

        $pdf = Pdf::loadView('student.payments.receipt-pdf', [
            'student'        => $student,
            'bills'          => $statement['bills'],
            'totals'         => $statement['totals'],
            'paymentHistory' => $statement['paymentHistory'],
            'termName'       => $termName,
            'sessionName'    => $sessionName,
            'schoolInfo'     => $schoolInfo,
            'logoBase64'     => $this->logoToBase64($schoolInfo),
            'pictureBase64'  => $this->imageToBase64ForPdf(
                DB::table('studentpicture')->where('studentid', $studentId)->orderByDesc('id')->value('picture')
            ),
            'receiptNo'      => $receiptNo,
            'className'      => $statement['class']->schoolclass,
            'generatedAt'    => Carbon::now()->format('d M Y, h:i A'),
        ])
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'dpi'                  => 150,
                'defaultFont'          => 'DejaVu Sans',
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
            ]);

        return $pdf->download($filename);
    }

    // =========================================================================
    // STATEMENT -- bills, totals, ledger history and arrears for one term
    // =========================================================================

    protected function buildStatement(Student $student, ?int $termId, ?int $sessionId): array
    {
        $studentId = (int) $student->id;
        $empty = [
            'class' => null, 'term' => null, 'session' => null,
            'bills' => collect(), 'paymentHistory' => collect(),
            'totals' => ['original' => 0, 'adjusted' => 0, 'paid' => 0, 'outstanding' => 0, 'savings' => 0],
            'scholarshipAssignment' => null, 'discountAssignments' => collect(),
            'arrears' => ['has_arrears' => false, 'total_arrears' => 0.0, 'groups' => [], 'bills' => []],
            'statementError' => null,
        ];

        $term    = $termId ? Schoolterm::find($termId, ['id', 'term']) : null;
        $session = $sessionId ? Schoolsession::find($sessionId, ['id', 'session']) : null;
        if (!$term || !$session) {
            return ['statementError' => 'Select a term and session to see your bills.'] + $empty;
        }

        $classId = $this->resolveClassId($studentId, $termId, $sessionId);
        $arrears = $this->arrears->getStudentArrears($studentId, $termId, $sessionId);

        if (!$classId) {
            return array_merge($empty, [
                'term' => $term, 'session' => $session, 'arrears' => $arrears,
                'statementError' => 'No class or fee record was found for you in ' . $term->term . ', ' . $session->session . '.',
            ]);
        }

        $classRow = DB::table('schoolclass')->leftJoin('schoolarm', 'schoolarm.id', '=', 'schoolclass.arm')
            ->where('schoolclass.id', $classId)->first(['schoolclass.id', 'schoolclass.schoolclass', 'schoolarm.arm']);
        $class = (object) [
            'id'          => $classId,
            'schoolclass' => trim(($classRow->schoolclass ?? '') . ' ' . ($classRow->arm ?? '')) ?: '—',
        ];

        // ── Bills: same filters as the bursar's screen ──────────────────────
        $rawBills = DB::table('school_bill_class_term_session as bcts')
            ->join('school_bill as sb', 'sb.id', '=', 'bcts.bill_id')
            ->where('bcts.class_id', $classId)
            ->where('bcts.termid_id', $termId)
            ->where('bcts.session_id', $sessionId)
            ->whereNull('bcts.deleted_at')
            ->where(function ($q) use ($student) {
                $q->whereNull('sb.statusId')
                  ->orWhere('sb.statusId', '')
                  ->orWhere('sb.statusId', 0)
                  ->orWhere('sb.statusId', $student->statusId);
            })
            ->orderBy('bcts.display_order')
            ->orderBy('sb.title')
            ->get([
                'sb.id', 'sb.title', 'sb.description', 'sb.bill_amount', 'sb.due_date', 'sb.category',
            ]);

        $now = now();
        $scholarshipAssignment = ScholarshipAssignment::where('student_id', $studentId)
            ->where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now))
            ->with('scholarship')
            ->first();
        $discountAssignments = DiscountAssignment::where('student_id', $studentId)
            ->where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $now))
            ->with('discount')
            ->get();

        // ── Amount paid per bill: from the ledger ───────────────────────────
        $paidByBill = DB::table('student_bill_payment_record as r')
            ->join('student_bill_payment as p', 'p.id', '=', 'r.student_bill_payment_id')
            ->where('p.student_id', $studentId)
            ->where('p.class_id', $classId)
            ->where('p.termid_id', $termId)
            ->where('p.session_id', $sessionId)
            ->whereNull('p.deleted_at')
            ->whereNull('r.deleted_at')
            ->groupBy('p.school_bill_id')
            ->select('p.school_bill_id', DB::raw('SUM(r.amount_paid) as total_paid'))
            ->pluck('total_paid', 'school_bill_id');

        // Legacy fallback only for bills with no ledger rows (same as admin).
        $bookPaid = DB::table('student_bill_payment_book')
            ->where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('term_id', $termId)
            ->where('session_id', $sessionId)
            ->pluck('amount_paid', 'school_bill_id');

        $bills  = collect();
        $totals = ['original' => 0, 'adjusted' => 0, 'paid' => 0, 'outstanding' => 0, 'savings' => 0];

        foreach ($rawBills as $bill) {
            $adj = $this->billAdjustment->buildBillAdjustment(
                $studentId, (int) $bill->id, (float) $bill->bill_amount, $scholarshipAssignment, $discountAssignments
            );

            $paid = isset($paidByBill[$bill->id])
                ? (float) $paidByBill[$bill->id]
                : (float) ($bookPaid[$bill->id] ?? 0);

            $payable  = (float) $adj['adjusted_amount'];
            $balance  = round(max(0, $payable - $paid), 2);
            $progress = $payable > 0 ? min(100, $paid / $payable * 100) : 100;

            $status = $payable <= 0 ? 'covered'
                : ($balance <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'));

            $bills->push([
                'id'                    => (int) $bill->id,
                'title'                 => $bill->title,
                'description'           => $bill->description,
                'category'              => $bill->category,
                'due_date'              => $bill->due_date ? Carbon::parse($bill->due_date)->format('d M Y') : null,
                'is_overdue'            => $bill->due_date && $balance > 0 && Carbon::parse($bill->due_date)->endOfDay()->isPast(),
                'original_amount'       => (float) $adj['original_amount'],
                'adjusted_amount'       => $payable,
                'scholarship_deduction' => (float) $adj['scholarship_deduction'],
                'scholarship_label'     => $adj['scholarship_label'],
                'discount_deduction'    => (float) $adj['discount_deduction'],
                'discount_labels'       => $adj['discount_labels'],
                'total_savings'         => (float) $adj['total_savings'],
                'savings'               => (float) $adj['total_savings'],
                'amount_paid'           => round($paid, 2),
                'balance'               => $balance,
                'progress'              => round($progress, 1),
                'status'                => $status,
                'is_paid'               => in_array($status, ['paid', 'covered'], true),
                'is_partial'            => $status === 'partial',
            ]);

            $totals['original']    += (float) $adj['original_amount'];
            $totals['adjusted']    += $payable;
            $totals['paid']        += $paid;
            $totals['outstanding'] += $balance;
            $totals['savings']     += (float) $adj['total_savings'];
        }
        $totals = array_map(fn ($v) => round($v, 2), $totals);

        return [
            'class'                 => $class,
            'term'                  => $term,
            'session'               => $session,
            'bills'                 => $bills,
            'totals'                => $totals,
            'paymentHistory'        => $this->paymentHistory($studentId, $termId, $sessionId),
            'scholarshipAssignment' => $scholarshipAssignment,
            'discountAssignments'   => $discountAssignments,
            'arrears'               => $arrears,
            'statementError'        => $rawBills->isEmpty() ? 'No fee bills have been set for your class this term yet.' : null,
        ];
    }

    /** One row per ledger transaction for the term (newest first). */
    protected function paymentHistory(int $studentId, int $termId, int $sessionId): Collection
    {
        return DB::table('student_bill_payment_record as r')
            ->join('student_bill_payment as p', 'p.id', '=', 'r.student_bill_payment_id')
            ->leftJoin('school_bill as sb', 'sb.id', '=', 'p.school_bill_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.generated_by')
            ->where('p.student_id', $studentId)
            ->where('p.termid_id', $termId)
            ->where('p.session_id', $sessionId)
            ->whereNull('p.deleted_at')
            ->whereNull('r.deleted_at')
            ->orderByDesc('r.created_at')
            ->orderByDesc('r.id')
            ->get([
                'r.id',
                'r.created_at as paid_at',
                'r.amount_paid',
                'r.amount_owed as balance_after',
                'r.complete_payment',
                'r.is_reversal',
                'r.invoiceNo as invoice_no',
                'r.transaction_reference as reference',
                'r.payment_channel',
                'p.payment_method',
                'sb.title as bill_title',
                'u.name as received_by',
            ])
            ->map(function ($r) {
                $r->paid_at = $r->paid_at ? Carbon::parse($r->paid_at) : null;
                $r->method  = $r->payment_channel ?: $r->payment_method ?: '—';
                $r->status  = $r->is_reversal ? 'reversal' : ((int) $r->complete_payment === 1 ? 'completed' : 'part');
                return $r;
            });
    }

    /** Amount paid per term for the session, from the ledger. */
    protected function buildPaymentTrend(int $studentId, ?int $sessionId): array
    {
        if (!$sessionId) return [];

        $sums = DB::table('student_bill_payment_record as r')
            ->join('student_bill_payment as p', 'p.id', '=', 'r.student_bill_payment_id')
            ->where('p.student_id', $studentId)
            ->where('p.session_id', $sessionId)
            ->whereNull('p.deleted_at')
            ->whereNull('r.deleted_at')
            ->groupBy('p.termid_id')
            ->select('p.termid_id', DB::raw('SUM(r.amount_paid) as total_paid'))
            ->pluck('total_paid', 'termid_id');

        $trend = [];
        foreach (Schoolterm::orderBy('id')->get(['id', 'term']) as $t) {
            if (isset($sums[$t->id]) && (float) $sums[$t->id] != 0.0) {
                $trend[$t->term] = round((float) $sums[$t->id], 2);
            }
        }
        return $trend;
    }

    // =========================================================================
    // PERIOD HELPERS
    // =========================================================================

    /**
     * Sessions the student actually has a record in (enrolment, fees,
     * payments or results), plus the current session. Newest first.
     */
    protected function periodOptions(int $studentId): array
    {
        $ids = collect()
            ->merge(DB::table('studentclass')->where('studentId', $studentId)->pluck('sessionid'))
            ->merge(DB::table('student_bill_payment_book')->where('student_id', $studentId)->pluck('session_id'))
            ->merge(DB::table('student_bill_payment')->where('student_id', $studentId)->whereNull('deleted_at')->pluck('session_id'))
            ->merge(DB::table('broadsheet_records')->where('student_id', $studentId)->pluck('session_id'))
            ->merge(Schoolsession::where('status', 'Current')->pluck('id'))
            ->map(fn ($v) => (int) $v)->filter()->unique();

        $sessions = Schoolsession::whereIn('id', $ids)->orderByDesc('id')->get(['id', 'session', 'status']);
        $terms    = Schoolterm::orderBy('id')->get(['id', 'term']);

        return [$sessions, $terms];
    }

    /** Selected session/term, defaulting to the current session and the student's latest term in it. */
    protected function resolvePeriod(Request $request, int $studentId, Collection $sessions): array
    {
        $sessionId = (int) $request->get('session_id');
        if (!$sessionId || !$sessions->contains('id', $sessionId)) {
            $sessionId = (int) ($sessions->firstWhere('status', 'Current')->id ?? $sessions->first()->id ?? 0);
        }

        $termId = (int) $request->get('term_id');
        if (!$termId) {
            $termId = (int) (
                DB::table('studentclass')->where('studentId', $studentId)->where('sessionid', $sessionId)->max('termid')
                ?: DB::table('student_bill_payment_book')->where('student_id', $studentId)->where('session_id', $sessionId)->max('term_id')
                ?: DB::table('student_bill_payment')->where('student_id', $studentId)->where('session_id', $sessionId)->whereNull('deleted_at')->max('termid_id')
                ?: Schoolterm::min('id')
            );
        }

        return [$sessionId ?: null, $termId ?: null];
    }

    /**
     * The class the student was in for this term: enrolment first, then the
     * payment book / payment rows (which record the class billed), then
     * results.
     */
    protected function resolveClassId(int $studentId, int $termId, int $sessionId): ?int
    {
        $id = DB::table('studentclass')->where('studentId', $studentId)->where('sessionid', $sessionId)
                ->orderByRaw('termid = ? DESC', [$termId])->orderByDesc('id')->value('schoolclassid')
            ?: DB::table('student_bill_payment_book')->where('student_id', $studentId)
                ->where('term_id', $termId)->where('session_id', $sessionId)->value('class_id')
            ?: DB::table('student_bill_payment')->where('student_id', $studentId)
                ->where('termid_id', $termId)->where('session_id', $sessionId)->whereNull('deleted_at')->value('class_id')
            ?: DB::table('broadsheet_records')->where('student_id', $studentId)
                ->where('session_id', $sessionId)->orderByDesc('id')->value('schoolclass_id');

        return $id ? (int) $id : null;
    }

    // =========================================================================
    // IMAGE HELPERS (PDF)
    // =========================================================================

    private function logoToBase64($schoolInfo): string
    {
        $placeholder = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80"><rect width="80" height="80" rx="40" fill="#0f2342"/><text x="40" y="46" text-anchor="middle" fill="white" font-family="Arial" font-size="14" font-weight="bold">SCH</text></svg>'
        );
        if (!$schoolInfo || empty($schoolInfo->school_logo)) return $placeholder;

        foreach ([
            storage_path('app/public/' . $schoolInfo->school_logo),
            public_path('storage/' . $schoolInfo->school_logo),
            public_path($schoolInfo->school_logo),
        ] as $path) {
            if (file_exists($path) && filesize($path) > 100) {
                return 'data:' . (mime_content_type($path) ?: 'image/jpeg') . ';base64,' . base64_encode(file_get_contents($path));
            }
        }
        return $placeholder;
    }

    private function imageToBase64ForPdf(?string $path): string
    {
        $placeholder = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="95" viewBox="0 0 80 95"><rect width="80" height="95" fill="#e2e8f0"/><circle cx="40" cy="32" r="18" fill="#94a3b8"/><rect x="20" y="56" width="40" height="28" rx="4" fill="#94a3b8"/></svg>'
        );
        if (!$path) return $placeholder;

        foreach ([
            public_path('storage/student_avatars/' . basename($path)),
            storage_path('app/public/student_avatars/' . basename($path)),
            public_path('storage/' . $path),
            storage_path('app/public/' . $path),
        ] as $fullPath) {
            if (file_exists($fullPath) && filesize($fullPath) > 100) {
                return 'data:' . (mime_content_type($fullPath) ?: 'image/jpeg') . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }
        return $placeholder;
    }
}
