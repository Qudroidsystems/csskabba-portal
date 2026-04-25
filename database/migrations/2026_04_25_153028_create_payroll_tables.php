<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Payroll Periods Table
        if (!Schema::hasTable('payroll_periods')) {
            Schema::create('payroll_periods', function (Blueprint $table) {
                $table->id();
                $table->string('period_name', 50);
                $table->string('month', 20);
                $table->integer('year');
                $table->date('start_date');
                $table->date('end_date');
                $table->date('payment_date');
                $table->enum('status', ['draft', 'processing', 'approved', 'paid', 'locked'])->default('draft');
                $table->decimal('total_gross_pay', 15, 2)->default(0);
                $table->decimal('total_employee_pension', 15, 2)->default(0);
                $table->decimal('total_employer_pension', 15, 2)->default(0);
                $table->decimal('total_tax', 15, 2)->default(0);
                $table->decimal('total_nhf', 15, 2)->default(0);
                $table->decimal('total_loan_deductions', 15, 2)->default(0);
                $table->decimal('total_other_deductions', 15, 2)->default(0);
                $table->decimal('total_net_pay', 15, 2)->default(0);
                $table->unsignedBigInteger('processed_by')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('journal_entry_id')->nullable();
                $table->timestamps();

                $table->index(['year', 'month']);
                $table->index('status');
            });
        }

        // Payroll Runs Table
        if (!Schema::hasTable('payroll_runs')) {
            Schema::create('payroll_runs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payroll_period_id');
                $table->unsignedBigInteger('staff_id');
                $table->unsignedBigInteger('salary_structure_id');

                // Earnings
                $table->decimal('basic_salary', 15, 2)->default(0);
                $table->decimal('housing_allowance', 15, 2)->default(0);
                $table->decimal('transport_allowance', 15, 2)->default(0);
                $table->decimal('meal_allowance', 15, 2)->default(0);
                $table->decimal('medical_allowance', 15, 2)->default(0);
                $table->decimal('utility_allowance', 15, 2)->default(0);
                $table->decimal('other_allowances', 15, 2)->default(0);
                $table->json('custom_allowances')->nullable();
                $table->decimal('overtime_pay', 15, 2)->default(0);
                $table->decimal('bonus', 15, 2)->default(0);
                $table->decimal('commission', 15, 2)->default(0);
                $table->decimal('total_earnings', 15, 2)->default(0);

                // Statutory Deductions
                $table->decimal('paye_tax', 15, 2)->default(0);
                $table->decimal('employee_pension', 15, 2)->default(0);
                $table->decimal('employer_pension', 15, 2)->default(0);
                $table->decimal('nhf', 15, 2)->default(0);
                $table->decimal('nsitf', 15, 2)->default(0);

                // Loan & Other Deductions
                $table->decimal('loan_repayment', 15, 2)->default(0);
                $table->json('loan_details')->nullable();
                $table->decimal('advance_repayment', 15, 2)->default(0);
                $table->json('advance_details')->nullable();
                $table->decimal('union_dues', 15, 2)->default(0);
                $table->decimal('cooperative_deductions', 15, 2)->default(0);
                $table->json('other_deductions')->nullable();
                $table->decimal('total_deductions', 15, 2)->default(0);

                // Net Pay
                $table->decimal('net_pay', 15, 2)->default(0);

                // Payment Details
                $table->string('bank_name')->nullable();
                $table->string('account_number')->nullable();
                $table->string('account_name')->nullable();
                $table->enum('payment_status', ['pending', 'processed', 'paid'])->default('pending');
                $table->timestamp('paid_at')->nullable();
                $table->string('transaction_reference')->nullable();

                $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
                $table->unsignedBigInteger('processed_by')->nullable();
                $table->timestamps();

                $table->foreign('payroll_period_id')->references('id')->on('payroll_periods')->onDelete('cascade');
                $table->foreign('staff_id')->references('id')->on('staffbioinfo')->onDelete('cascade');
                $table->foreign('salary_structure_id')->references('id')->on('staff_salary_structures');

                $table->index(['payroll_period_id', 'staff_id']);
                $table->index('payment_status');
            });
        }

        // Staff Salary Structures Table
        if (!Schema::hasTable('staff_salary_structures')) {
            Schema::create('staff_salary_structures', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('staff_id');
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->decimal('basic_salary', 15, 2);
                $table->decimal('housing_allowance', 15, 2)->default(0);
                $table->decimal('transport_allowance', 15, 2)->default(0);
                $table->decimal('meal_allowance', 15, 2)->default(0);
                $table->decimal('medical_allowance', 15, 2)->default(0);
                $table->decimal('utility_allowance', 15, 2)->default(0);
                $table->decimal('other_allowances', 15, 2)->default(0);
                $table->json('custom_allowances')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('staff_id')->references('id')->on('staffbioinfo')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users');
                $table->index(['staff_id', 'is_active']);
                $table->index(['effective_from', 'effective_to']);
            });
        }

        // Staff Payments Table
        if (!Schema::hasTable('staff_payments')) {
            Schema::create('staff_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('staff_id');
                $table->unsignedBigInteger('payroll_run_id')->nullable();
                $table->string('payment_reference')->unique();
                $table->enum('payment_type', ['salary', 'bonus', 'loan_disbursement', 'reimbursement', 'advance', 'other'])->default('salary');
                $table->decimal('amount', 15, 2);
                $table->date('payment_date');
                $table->enum('payment_method', ['bank_transfer', 'cash', 'cheque']);
                $table->string('bank_name')->nullable();
                $table->string('account_number')->nullable();
                $table->string('account_name')->nullable();
                $table->string('transaction_ref')->nullable();
                $table->text('purpose')->nullable();
                $table->string('attachment')->nullable();
                $table->enum('payment_status', ['pending', 'processed', 'paid', 'failed', 'reversed'])->default('pending');
                $table->unsignedBigInteger('created_by');
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('reversed_by')->nullable();
                $table->timestamp('reversed_at')->nullable();
                $table->text('reversal_reason')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('staff_id')->references('id')->on('staffbioinfo')->onDelete('cascade');
                $table->foreign('payroll_run_id')->references('id')->on('payroll_runs');
                $table->foreign('created_by')->references('id')->on('users');
                $table->foreign('reversed_by')->references('id')->on('users');

                $table->index('payment_reference');
                $table->index('payment_date');
                $table->index('payment_status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('staff_payments');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('staff_salary_structures');
    }
};
