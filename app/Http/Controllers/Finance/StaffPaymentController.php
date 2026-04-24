<?php
// app/Http/Controllers/Finance/StaffPaymentController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\StaffRecord;
use App\Models\StaffPayment;
use App\Models\PayrollRun;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\Payroll\NigerianPayrollService;
use App\Services\Accounting\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class StaffPaymentController extends Controller
{
    protected $payrollService;
    protected $accountingService;

    public function __construct(
        NigerianPayrollService $payrollService,
        AccountingService $accountingService
    ) {
        $this->payrollService = $payrollService;
        $this->accountingService = $accountingService;

        $this->middleware('permission:View staff payments', ['only' => ['index', 'staffDashboard', 'getPaymentHistory']]);
        $this->middleware('permission:Create staff payment', ['only' => ['recordManualPayment']]);
        $this->middleware('permission:Reverse staff payment', ['only' => ['reversePayment']]);
    }

    /**
     * Admin index with DataTable AJAX.
     */
    public function index(Request $request)
    {
        $pagetitle = 'Staff Payments Management';

        if ($request->ajax()) {
            $payments = StaffPayment::with(['staff.user', 'payrollRun'])
                ->select('staff_payments.*');

            return DataTables::of($payments)
                ->addIndexColumn()
                ->addColumn('staff_name', function($payment) {
                    return $payment->staff->user->name ?? 'N/A';
                })
                ->addColumn('staff_id', function($payment) {
                    return $payment->staff->employmentid ?? 'N/A';
                })
                ->addColumn('formatted_amount', function($payment) {
                    return '₦' . number_format($payment->amount, 2);
                })
                ->addColumn('status_badge', function($payment) {
                    $badges = [
                        'pending' => '<span class="badge bg-warning">Pending</span>',
                        'processed' => '<span class="badge bg-info">Processed</span>',
                        'paid' => '<span class="badge bg-success">Paid</span>',
                        'failed' => '<span class="badge bg-danger">Failed</span>',
                    ];
                    return $badges[$payment->payment_status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('action', function($payment) {
                    $buttons = '<button class="btn btn-sm btn-info view-payment me-1" data-id="'.$payment->id.'"><i class="ri-eye-line"></i></button>';
                    if ($payment->payment_status !== 'paid') {
                        $buttons .= '<button class="btn btn-sm btn-success mark-paid me-1" data-id="'.$payment->id.'"><i class="ri-check-line"></i></button>';
                        $buttons .= '<button class="btn btn-sm btn-danger reverse-payment" data-id="'.$payment->id.'"><i class="ri-refund-line"></i></button>';
                    }
                    return $buttons;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $staff = StaffRecord::with('user')->get();
        $payrollPeriods = PayrollPeriod::orderBy('id', 'desc')->get();

        return view('finance.staff.index', compact('pagetitle', 'staff', 'payrollPeriods'));
    }

    /**
     * Staff dashboard (staff view with AJAX).
     */
    public function staffDashboard(Request $request)
    {
        $staff = Auth::user()->staff;

        if (!$staff) {
            return redirect()->route('dashboard')->with('error', 'Staff record not found');
        }

        if ($request->ajax()) {
            $payments = StaffPayment::where('staff_id', $staff->id)
                ->orderBy('payment_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'payments' => $payments->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'reference' => $payment->payment_reference,
                        'amount' => '₦' . number_format($payment->amount, 2),
                        'date' => $payment->payment_date->format('d M, Y'),
                        'type' => ucfirst($payment->payment_type),
                        'method' => ucfirst(str_replace('_', ' ', $payment->payment_method)),
                        'status' => ucfirst($payment->payment_status),
                        'purpose' => $payment->purpose,
                    ];
                })
            ]);
        }

        $payments = StaffPayment::where('staff_id', $staff->id)
            ->orderBy('payment_date', 'desc')
            ->paginate(20);

        $payrollHistory = PayrollRun::where('staff_id', $staff->id)
            ->where('payment_status', 'paid')
            ->orderBy('created_at', 'desc')
            ->get();

        $loanSummary = $this->payrollService->getActiveLoanDeductions($staff->id);

        $pagetitle = 'My Payment Dashboard';

        return view('finance.staff.payment-dashboard', compact(
            'pagetitle', 'staff', 'payments', 'payrollHistory', 'loanSummary'
        ));
    }

    /**
     * Record manual payment (AJAX).
     */
    public function recordManualPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:staff_records,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cash,cheque',
            'payment_type' => 'required|in:salary,bonus,loan_disbursement,reimbursement,advance',
            'purpose' => 'required|string|min:5',
            'bank_name' => 'required_if:payment_method,bank_transfer',
            'account_number' => 'required_if:payment_method,bank_transfer',
            'transaction_ref' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('staff-payments', 'public');
            }

            $payment = StaffPayment::create([
                'staff_id' => $request->staff_id,
                'payment_reference' => $this->generatePaymentReference(),
                'payment_type' => $request->payment_type,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'transaction_ref' => $request->transaction_ref,
                'purpose' => $request->purpose,
                'attachment' => $attachmentPath,
                'payment_status' => 'processed',
                'created_by' => Auth::id(),
            ]);

            // Create accounting entry
            $this->createPaymentJournalEntry($payment);

            DB::commit();

            // Send notification to staff (optional)
            // $this->sendPaymentNotification($payment);

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully!',
                'data' => $payment
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment history (AJAX).
     */
    public function getPaymentHistory(Request $request)
    {
        $staffId = $request->input('staff_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $paymentType = $request->input('payment_type');

        $query = StaffPayment::with(['staff.user']);

        if ($staffId) {
            $query->where('staff_id', $staffId);
        }
        if ($startDate) {
            $query->where('payment_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('payment_date', '<=', $endDate);
        }
        if ($paymentType) {
            $query->where('payment_type', $paymentType);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'payments' => $payments->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'reference' => $payment->payment_reference,
                    'staff_name' => $payment->staff->user->name ?? 'N/A',
                    'amount' => number_format($payment->amount, 2),
                    'date' => $payment->payment_date->format('Y-m-d'),
                    'type' => ucfirst($payment->payment_type),
                    'method' => ucfirst(str_replace('_', ' ', $payment->payment_method)),
                    'status' => ucfirst($payment->payment_status),
                    'purpose' => $payment->purpose,
                ];
            })
        ]);
    }

    /**
     * Reverse payment (AJAX).
     */
    public function reversePayment(Request $request, $paymentId)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $payment = StaffPayment::findOrFail($paymentId);

            if ($payment->payment_status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reverse a payment that has already been disbursed'
                ], 400);
            }

            $payment->update([
                'payment_status' => 'reversed',
                'reversal_reason' => $request->reason,
                'reversed_by' => Auth::id(),
                'reversed_at' => now(),
            ]);

            // Create reversal accounting entry
            $this->createReversalJournalEntry($payment);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment reversed successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reverse payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark payment as paid (AJAX).
     */
    public function markAsPaid(Request $request, $paymentId)
    {
        DB::beginTransaction();
        try {
            $payment = StaffPayment::findOrFail($paymentId);

            $payment->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment marked as paid successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status'
            ], 500);
        }
    }

    /**
     * Generate payment reference number.
     */
    private function generatePaymentReference()
    {
        return 'SP-' . date('Ymd') . '-' . strtoupper(uniqid());
    }

    /**
     * Create accounting journal entry for payment.
     */
    private function createPaymentJournalEntry($payment)
    {
        // Get account IDs
        $expenseAccount = \App\Models\ChartOfAccount::where('account_code', '5000')->first(); // Staff Salaries
        $bankAccount = \App\Models\ChartOfAccount::where('account_code', '1020')->first(); // Bank Account

        if (!$expenseAccount || !$bankAccount) {
            return;
        }

        $this->accountingService->createJournalEntry([
            'entry_date' => $payment->payment_date,
            'entry_type' => 'payment',
            'description' => $payment->purpose,
            'reference_id' => $payment->id,
            'reference_type' => StaffPayment::class,
        ], [
            [
                'account_id' => $expenseAccount->id,
                'debit' => $payment->amount,
                'credit' => 0,
                'narration' => $payment->purpose,
            ],
            [
                'account_id' => $bankAccount->id,
                'debit' => 0,
                'credit' => $payment->amount,
                'narration' => 'Payment to staff via ' . $payment->payment_method,
            ]
        ]);
    }

    /**
     * Create reversal journal entry.
     */
    private function createReversalJournalEntry($payment)
    {
        $expenseAccount = \App\Models\ChartOfAccount::where('account_code', '5000')->first();
        $bankAccount = \App\Models\ChartOfAccount::where('account_code', '1020')->first();

        if (!$expenseAccount || !$bankAccount) {
            return;
        }

        $this->accountingService->createJournalEntry([
            'entry_date' => now(),
            'entry_type' => 'reversal',
            'description' => 'Reversal of payment #' . $payment->payment_reference . ' - Reason: ' . $payment->reversal_reason,
            'reference_id' => $payment->id,
            'reference_type' => StaffPayment::class,
        ], [
            [
                'account_id' => $expenseAccount->id,
                'debit' => 0,
                'credit' => $payment->amount,
                'narration' => 'Reversal of payment',
            ],
            [
                'account_id' => $bankAccount->id,
                'debit' => $payment->amount,
                'credit' => 0,
                'narration' => 'Reversal credit',
            ]
        ]);
    }
}
