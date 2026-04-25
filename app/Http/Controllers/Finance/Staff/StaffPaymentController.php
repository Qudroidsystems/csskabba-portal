<?php
// app/Http/Controllers/Finance/StaffPaymentController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaffPaymentController extends Controller
{
    /**
     * Display list of staff payments (Admin view).
     */
    public function index()
    {
        echo "hi";..
    }

    /**
     * Staff dashboard view (for staff members).
     */
    public function staffDashboard()
    {
        try {
            $pagetitle = 'My Payment Dashboard';

            // Simple test data
            $staff = null;
            $payments = collect([]);
            $payrollHistory = collect([]);
            $loanSummary = ['total_deduction' => 0, 'loans' => []];
            $stats = ['total_paid' => 0, 'total_pending' => 0, 'payment_count' => 0];

            return view('finance.staff.payment-dashboard', compact(
                'pagetitle', 'staff', 'payments', 'payrollHistory', 'loanSummary', 'stats'
            ));
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine();
        }
    }
}
