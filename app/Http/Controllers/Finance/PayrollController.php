<?php
// app/Http/Controllers/Finance/PayrollController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\StaffRecord;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\StaffSalaryStructure;
use App\Models\StaffPayment;
use App\Services\Payroll\NigerianPayrollService;
use App\Services\Accounting\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PayrollController extends Controller
{
    protected $payrollService;
    protected $accountingService;

    public function __construct(
        NigerianPayrollService $payrollService,
        AccountingService $accountingService
    ) {
        $this->payrollService = $payrollService;
        $this->accountingService = $accountingService;

        $this->middleware('permission:View payroll', ['only' => ['periods', 'getPayrollRuns', 'showPayrollRun', 'summaryReport']]);
        $this->middleware('permission:Process payroll', ['only' => ['processPayroll']]);
        $this->middleware('permission:Approve payroll', ['only' => ['approvePayroll']]);
        $this->middleware('permission:View payslip', ['only' => ['showPayrollRun']]);
        $this->middleware('permission:Download payslip', ['only' => ['exportPayroll']]);
    }

    /**
     * Display payroll periods with DataTable AJAX.
     */
    public function periods(Request $request)
    {
        $pagetitle = 'Payroll Management';

        if ($request->ajax()) {
            $periods = PayrollPeriod::with(['processedBy', 'approvedBy'])
                ->orderBy('id', 'desc');

            return DataTables::of($periods)
                ->addIndexColumn()
                ->addColumn('period_info', function($period) {
                    return $period->period_name . ' (' . $period->start_date->format('M d') . ' - ' . $period->end_date->format('M d, Y') . ')';
                })
                ->addColumn('total_gross', function($period) {
                    return '₦' . number_format($period->total_gross_pay, 2);
                })
                ->addColumn('total_net', function($period) {
                    return '₦' . number_format($period->total_net_pay, 2);
                })
                ->addColumn('status_badge', function($period) {
                    return $period->status_badge;
                })
                ->addColumn('action', function($period) {
                    $buttons = '';

                    if ($period->status === 'draft') {
                        $buttons .= '<button class="btn btn-sm btn-primary process-payroll me-1" data-id="'.$period->id.'"><i class="ri-play-line"></i> Process</button>';
                    }
                    if ($period->status === 'processing') {
                        $buttons .= '<button class="btn btn-sm btn-success approve-payroll me-1" data-id="'.$period->id.'"><i class="ri-check-line"></i> Approve</button>';
                    }
                    if ($period->status === 'approved') {
                        $buttons .= '<button class="btn btn-sm btn-warning mark-paid me-1" data-id="'.$period->id.'"><i class="ri-bank-card-line"></i> Mark Paid</button>';
                    }

                    $buttons .= '<a href="' . route('payroll.runs', $period->id) . '" class="btn btn-sm btn-info"><i class="ri-eye-line"></i> View</a>';
                    $buttons .= '<button class="btn btn-sm btn-secondary export-payroll ms-1" data-id="'.$period->id.'"><i class="ri-download-line"></i> Export</button>';

                    return $buttons;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $currentPeriod = PayrollPeriod::where('status', 'draft')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        return view('finance.payroll.periods', compact('pagetitle', 'currentPeriod'));
    }

    /**
     * Create new payroll period (AJAX).
     */
    public function createPeriod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
            'payment_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if period already exists
        $exists = PayrollPeriod::where('month', $request->month)
            ->where('year', $request->year)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll period for ' . date('F Y', mktime(0, 0, 0, $request->month, 1, $request->year)) . ' already exists'
            ], 422);
        }

        $startDate = date('Y-m-d', strtotime("{$request->year}-{$request->month}-01"));
        $endDate = date('Y-m-t', strtotime($startDate));
        $periodName = date('F Y', strtotime($startDate));

        $period = PayrollPeriod::create([
            'period_name' => $periodName,
            'month' => $request->month,
            'year' => $request->year,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payment_date' => $request->payment_date,
            'status' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payroll period created successfully!',
            'data' => $period
        ]);
    }

    /**
     * Process payroll for a period (AJAX).
     */
    public function processPayroll(Request $request, $periodId)
    {
        try {
            $result = $this->payrollService->processPayroll($periodId);

            return response()->json([
                'success' => true,
                'message' => 'Payroll processed successfully!',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve payroll and create accounting entries (AJAX).
     */
    public function approvePayroll(Request $request, $periodId)
    {
        try {
            $result = $this->payrollService->approvePayroll($periodId);

            return response()->json([
                'success' => true,
                'message' => 'Payroll approved and journal entry created!',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lock payroll period (AJAX).
     */
    public function lockPeriod(Request $request, $periodId)
    {
        $period = PayrollPeriod::findOrFail($periodId);

        $period->update(['status' => 'locked']);

        return response()->json([
            'success' => true,
            'message' => 'Payroll period locked successfully!'
        ]);
    }

    /**
     * Get payroll runs for a period with DataTable AJAX.
     */
    public function getPayrollRuns(Request $request, $periodId)
    {
        $period = PayrollPeriod::findOrFail($periodId);
        $pagetitle = 'Payroll Details - ' . $period->period_name;

        if ($request->ajax()) {
            $runs = PayrollRun::where('payroll_period_id', $periodId)
                ->with(['staff.user', 'salaryStructure'])
                ->orderBy('staff_id');

            return DataTables::of($runs)
                ->addIndexColumn()
                ->addColumn('staff_name', function($run) {
                    return $run->staff->user->name ?? 'N/A';
                })
                ->addColumn('staff_id', function($run) {
                    return $run->staff->employmentid ?? 'N/A';
                })
                ->addColumn('gross_pay', function($run) {
                    return '₦' . number_format($run->total_earnings, 2);
                })
                ->addColumn('deductions', function($run) {
                    return '₦' . number_format($run->total_deductions, 2);
                })
                ->addColumn('net_pay', function($run) {
                    return '₦' . number_format($run->net_pay, 2);
                })
                ->addColumn('status_badge', function($run) {
                    return $run->status_badge;
                })
                ->addColumn('action', function($run) use ($period) {
                    $buttons = '<a href="' . route('payroll.run.show', $run->id) . '" class="btn btn-sm btn-info"><i class="ri-eye-line"></i> View</a>';

                    if ($period->status === 'approved' && $run->payment_status !== 'paid') {
                        $buttons .= '<button class="btn btn-sm btn-success mark-staff-paid ms-1" data-id="'.$run->id.'"><i class="ri-bank-card-line"></i> Mark Paid</button>';
                    }

                    return $buttons;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $summary = [
            'total_staff' => PayrollRun::where('payroll_period_id', $periodId)->count(),
            'total_gross' => $period->total_gross_pay,
            'total_deductions' => $period->total_net_pay ? ($period->total_gross_pay - $period->total_net_pay) : 0,
            'total_net' => $period->total_net_pay,
            'total_tax' => $period->total_tax,
            'total_pension' => $period->total_employee_pension,
        ];

        return view('finance.payroll.runs', compact('pagetitle', 'period', 'summary'));
    }

    /**
     * Show individual payroll run (payslip view).
     */
    public function showPayrollRun($payrollRunId)
    {
        $payrollRun = PayrollRun::with(['staff.user', 'payrollPeriod', 'salaryStructure'])
            ->findOrFail($payrollRunId);

        // Verify access
        $user = Auth::user();
        $staff = $user->staff;

        if (!$staff || ($staff->id != $payrollRun->staff_id && !$user->hasPermissionTo('View payroll'))) {
            abort(403);
        }

        $earnings = [
            ['name' => 'Basic Salary', 'amount' => $payrollRun->basic_salary],
            ['name' => 'Housing Allowance', 'amount' => $payrollRun->housing_allowance],
            ['name' => 'Transport Allowance', 'amount' => $payrollRun->transport_allowance],
            ['name' => 'Meal Allowance', 'amount' => $payrollRun->meal_allowance],
            ['name' => 'Medical Allowance', 'amount' => $payrollRun->medical_allowance],
            ['name' => 'Utility Allowance', 'amount' => $payrollRun->utility_allowance],
            ['name' => 'Other Allowances', 'amount' => $payrollRun->other_allowances],
        ];

        $customAllowances = $payrollRun->custom_allowances ?? [];
        foreach ($customAllowances as $allowance) {
            $earnings[] = ['name' => $allowance['name'], 'amount' => $allowance['amount']];
        }

        $deductions = [
            ['name' => 'PAYE Tax', 'amount' => $payrollRun->paye_tax],
            ['name' => 'Pension (Employee)', 'amount' => $payrollRun->employee_pension],
            ['name' => 'NHF', 'amount' => $payrollRun->nhf],
            ['name' => 'Loan Repayment', 'amount' => $payrollRun->loan_repayment],
            ['name' => 'Salary Advance', 'amount' => $payrollRun->advance_repayment],
        ];

        $employerContributions = [
            ['name' => 'Pension (Employer)', 'amount' => $payrollRun->employer_pension],
            ['name' => 'NSITF', 'amount' => $payrollRun->nsitf],
        ];

        $schoolInfo = \App\Models\SchoolInformation::first();

        $pagetitle = 'Payslip - ' . $payrollRun->staff->user->name . ' (' . $payrollRun->payrollPeriod->period_name . ')';

        return view('finance.payroll.payslip', compact(
            'pagetitle', 'payrollRun', 'earnings', 'deductions',
            'employerContributions', 'schoolInfo'
        ));
    }

    /**
     * Process staff payment (AJAX).
     */
    public function processStaffPayment(Request $request, $payrollRunId)
    {
        $validator = Validator::make($request->all(), [
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cash,cheque',
            'bank_name' => 'required_if:payment_method,bank_transfer',
            'account_number' => 'required_if:payment_method,bank_transfer',
            'transaction_ref' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $payment = $this->payrollService->recordStaffPayment(
                $request->staff_id ?? null,
                $payrollRunId,
                $request->all()
            );

            return response()->json([
                'success' => true,
                'message' => 'Staff payment recorded successfully!',
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Summary report with AJAX.
     */
    public function summaryReport(Request $request)
    {
        $pagetitle = 'Payroll Summary Report';

        if ($request->ajax()) {
            $year = $request->input('year', date('Y'));

            $data = PayrollPeriod::where('year', $year)
                ->where('status', 'paid')
                ->select(
                    'month',
                    'period_name',
                    'total_gross_pay',
                    'total_tax',
                    'total_employee_pension',
                    'total_employer_pension',
                    'total_net_pay'
                )
                ->orderBy('month')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data,
                'year' => $year
            ]);
        }

        $years = PayrollPeriod::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        $currentYear = date('Y');

        return view('finance.payroll.summary', compact('pagetitle', 'years', 'currentYear'));
    }

    /**
     * Export payroll to Excel.
     */
    public function exportPayroll(Request $request, $periodId)
    {
        $period = PayrollPeriod::findOrFail($periodId);
        $runs = PayrollRun::where('payroll_period_id', $periodId)
            ->with(['staff.user'])
            ->get();

        $filename = "payroll_{$period->period_name}_{$period->year}.xlsx";

        // Excel export logic here
        // return Excel::download(new PayrollExport($runs, $period), $filename);

        return response()->json([
            'success' => true,
            'message' => 'Export initiated',
            'download_url' => route('payroll.export.download', $periodId)
        ]);
    }

    /**
     * Get staff salary structures (AJAX).
     */
    public function salaryStructures(Request $request)
    {
        $pagetitle = 'Staff Salary Structures';

        if ($request->ajax()) {
            $structures = StaffSalaryStructure::with(['staff.user', 'createdBy'])
                ->orderBy('id', 'desc');

            return DataTables::of($structures)
                ->addIndexColumn()
                ->addColumn('staff_name', function($structure) {
                    return $structure->staff->user->name ?? 'N/A';
                })
                ->addColumn('basic_salary', function($structure) {
                    return '₦' . number_format($structure->basic_salary, 2);
                })
                ->addColumn('total_earnings', function($structure) {
                    return '₦' . number_format($structure->total_earnings, 2);
                })
                ->addColumn('effective_period', function($structure) {
                    $from = $structure->effective_from->format('d M Y');
                    $to = $structure->effective_to ? $structure->effective_to->format('d M Y') : 'Present';
                    return $from . ' - ' . $to;
                })
                ->addColumn('action', function($structure) {
                    $buttons = '<button class="btn btn-sm btn-primary edit-structure me-1" data-id="'.$structure->id.'"><i class="ri-pencil-line"></i></button>';
                    $buttons .= '<button class="btn btn-sm btn-danger delete-structure" data-id="'.$structure->id.'"><i class="ri-delete-bin-line"></i></button>';
                    return $buttons;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $staff = StaffRecord::with('user')->get();

        return view('finance.payroll.structures', compact('pagetitle', 'staff'));
    }

    /**
     * Store salary structure (AJAX).
     */
    public function storeSalaryStructure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|exists:staff_records,id',
            'basic_salary' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Deactivate previous structures
        StaffSalaryStructure::where('staff_id', $request->staff_id)
            ->where('is_active', true)
            ->update(['is_active' => false, 'effective_to' => now()]);

        $structure = StaffSalaryStructure::create([
            'staff_id' => $request->staff_id,
            'basic_salary' => $request->basic_salary,
            'housing_allowance' => $request->housing_allowance ?? 0,
            'transport_allowance' => $request->transport_allowance ?? 0,
            'meal_allowance' => $request->meal_allowance ?? 0,
            'medical_allowance' => $request->medical_allowance ?? 0,
            'utility_allowance' => $request->utility_allowance ?? 0,
            'other_allowances' => $request->other_allowances ?? 0,
            'custom_allowances' => $request->custom_allowances,
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Salary structure created successfully!',
            'data' => $structure
        ]);
    }

    /**
     * Update salary structure (AJAX).
     */
    public function updateSalaryStructure(Request $request, $id)
    {
        $structure = StaffSalaryStructure::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'basic_salary' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $structure->update([
            'basic_salary' => $request->basic_salary,
            'housing_allowance' => $request->housing_allowance ?? 0,
            'transport_allowance' => $request->transport_allowance ?? 0,
            'meal_allowance' => $request->meal_allowance ?? 0,
            'medical_allowance' => $request->medical_allowance ?? 0,
            'utility_allowance' => $request->utility_allowance ?? 0,
            'other_allowances' => $request->other_allowances ?? 0,
            'custom_allowances' => $request->custom_allowances,
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Salary structure updated successfully!',
            'data' => $structure
        ]);
    }

    /**
     * Delete salary structure (AJAX).
     */
    public function destroySalaryStructure($id)
    {
        $structure = StaffSalaryStructure::findOrFail($id);
        $structure->delete();

        return response()->json([
            'success' => true,
            'message' => 'Salary structure deleted successfully!'
        ]);
    }

    /**
     * Statutory remittance report.
     */
    public function statutoryReport(Request $request)
    {
        $pagetitle = 'Statutory Remittance Report';

        $year = $request->input('year', date('Y'));

        $data = PayrollPeriod::where('year', $year)
            ->where('status', 'approved')
            ->select(
                'month',
                'period_name',
                'total_tax as paye',
                'total_employee_pension as employee_pension',
                'total_employer_pension as employer_pension',
                'total_nhf as nhf'
            )
            ->orderBy('month')
            ->get();

        return view('finance.payroll.statutory', compact('pagetitle', 'data', 'year'));
    }
}
