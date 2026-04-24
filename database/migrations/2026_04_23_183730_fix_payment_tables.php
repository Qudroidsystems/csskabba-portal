<?php
// database/migrations/2026_04_23_183618_fix_payment_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix student_bill_payment table
        Schema::table('student_bill_payment', function (Blueprint $table) {
            // Add missing columns
            if (!Schema::hasColumn('student_bill_payment', 'total_paid')) {
                $table->decimal('total_paid', 15, 2)->default(0)->after('payment_method');
            }
            if (!Schema::hasColumn('student_bill_payment', 'total_balance')) {
                $table->decimal('total_balance', 15, 2)->default(0)->after('total_paid');
            }
            if (!Schema::hasColumn('student_bill_payment', 'last_payment_date')) {
                $table->timestamp('last_payment_date')->nullable()->after('total_balance');
            }
            if (!Schema::hasColumn('student_bill_payment', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'partial', 'completed', 'failed', 'refunded'])->default('pending')->after('status');
            }
            if (!Schema::hasColumn('student_bill_payment', 'session_token')) {
                $table->string('session_token', 100)->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('student_bill_payment', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Fix data types for foreign keys using direct ALTER
        $this->fixColumnTypes('student_bill_payment', ['student_id', 'school_bill_id', 'class_id', 'termid_id', 'session_id', 'generated_by']);

        // Add foreign keys with short names
        Schema::table('student_bill_payment', function (Blueprint $table) {
            // Check if columns exist and add foreign keys
            if (Schema::hasColumn('student_bill_payment', 'student_id')) {
                $table->foreign('student_id', 'fk_sbp_student')->references('id')->on('studentRegistration')->onDelete('cascade');
            }
            if (Schema::hasColumn('student_bill_payment', 'school_bill_id')) {
                $table->foreign('school_bill_id', 'fk_sbp_bill')->references('id')->on('school_bill')->onDelete('cascade');
            }
            if (Schema::hasColumn('student_bill_payment', 'class_id')) {
                $table->foreign('class_id', 'fk_sbp_class')->references('id')->on('schoolclass');
            }
            if (Schema::hasColumn('student_bill_payment', 'termid_id')) {
                $table->foreign('termid_id', 'fk_sbp_term')->references('id')->on('schoolterm');
            }
            if (Schema::hasColumn('student_bill_payment', 'session_id')) {
                $table->foreign('session_id', 'fk_sbp_session')->references('id')->on('schoolsession');
            }
            if (Schema::hasColumn('student_bill_payment', 'generated_by')) {
                $table->foreign('generated_by', 'fk_sbp_generated')->references('id')->on('users');
            }

            // Add indexes with short names
            $table->index(['student_id', 'termid_id', 'session_id'], 'idx_sbp_student_term_session');
            $table->index('payment_status', 'idx_sbp_status');
        });

        // Fix student_bill_payment_record table
        Schema::table('student_bill_payment_record', function (Blueprint $table) {
            // Add missing columns
            if (!Schema::hasColumn('student_bill_payment_record', 'last_payment')) {
                $table->decimal('last_payment', 15, 2)->default(0)->after('amount_paid');
            }
            if (!Schema::hasColumn('student_bill_payment_record', 'is_reversal')) {
                $table->boolean('is_reversal')->default(false)->after('complete_payment');
            }
            if (!Schema::hasColumn('student_bill_payment_record', 'reversal_reason')) {
                $table->text('reversal_reason')->nullable()->after('is_reversal');
            }
            if (!Schema::hasColumn('student_bill_payment_record', 'invoiceNo')) {
                $table->string('invoiceNo', 100)->nullable()->after('reversal_reason');
            }
            if (!Schema::hasColumn('student_bill_payment_record', 'transaction_reference')) {
                $table->string('transaction_reference', 100)->nullable()->after('invoiceNo');
            }
            if (!Schema::hasColumn('student_bill_payment_record', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }

            // Fix column type
            $table->unsignedBigInteger('student_bill_payment_id')->change();

            // Add foreign key with short name
            $table->foreign('student_bill_payment_id', 'fk_sbpr_payment')->references('id')->on('student_bill_payment')->onDelete('cascade');
        });

        // Fix student_bill_payment_book table
        Schema::table('student_bill_payment_book', function (Blueprint $table) {
            // Fix data types
            if (Schema::hasColumn('student_bill_payment_book', 'amount_paid')) {
                $table->decimal('amount_paid', 15, 2)->default(0)->change();
            }
            if (Schema::hasColumn('student_bill_payment_book', 'amount_owed')) {
                $table->decimal('amount_owed', 15, 2)->default(0)->change();
            }

            // Add missing columns
            if (!Schema::hasColumn('student_bill_payment_book', 'original_amount')) {
                $table->decimal('original_amount', 15, 2)->default(0)->after('school_bill_id');
            }
            if (!Schema::hasColumn('student_bill_payment_book', 'scholarship_deduction')) {
                $table->decimal('scholarship_deduction', 15, 2)->default(0)->after('original_amount');
            }
            if (!Schema::hasColumn('student_bill_payment_book', 'discount_deduction')) {
                $table->decimal('discount_deduction', 15, 2)->default(0)->after('scholarship_deduction');
            }
            if (!Schema::hasColumn('student_bill_payment_book', 'adjusted_amount')) {
                $table->decimal('adjusted_amount', 15, 2)->default(0)->after('discount_deduction');
            }
        });

        // Fix student_bill_invoice table
        Schema::table('student_bill_invoice', function (Blueprint $table) {
            // Fix data types
            $this->fixColumnTypes('student_bill_invoice', ['student_id', 'school_bill_id', 'class_id', 'termid_id', 'session_id', 'generated_by']);

            // Add PDF path column
            if (!Schema::hasColumn('student_bill_invoice', 'pdf_path')) {
                $table->string('pdf_path')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('student_bill_invoice', 'amount')) {
                $table->decimal('amount', 15, 2)->default(0)->after('pdf_path');
            }
            if (!Schema::hasColumn('student_bill_invoice', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    /**
     * Fix column types from string to unsigned big integer
     */
    private function fixColumnTypes($table, $columns): void
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                try {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NOT NULL");
                } catch (\Exception $e) {
                    // If column has data that can't be converted, try with conversion
                    DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NULL");
                }
            }
        }
    }

    public function down(): void
    {
        // Remove foreign keys
        Schema::table('student_bill_payment', function (Blueprint $table) {
            $table->dropForeign('fk_sbp_student');
            $table->dropForeign('fk_sbp_bill');
            $table->dropForeign('fk_sbp_class');
            $table->dropForeign('fk_sbp_term');
            $table->dropForeign('fk_sbp_session');
            $table->dropForeign('fk_sbp_generated');
            $table->dropIndex('idx_sbp_student_term_session');
            $table->dropIndex('idx_sbp_status');

            $table->dropColumnIfExists('total_paid');
            $table->dropColumnIfExists('total_balance');
            $table->dropColumnIfExists('last_payment_date');
            $table->dropColumnIfExists('payment_status');
            $table->dropColumnIfExists('session_token');
            $table->dropColumnIfExists('deleted_at');
        });

        Schema::table('student_bill_payment_record', function (Blueprint $table) {
            $table->dropForeign('fk_sbpr_payment');
            $table->dropColumnIfExists('last_payment');
            $table->dropColumnIfExists('is_reversal');
            $table->dropColumnIfExists('reversal_reason');
            $table->dropColumnIfExists('invoiceNo');
            $table->dropColumnIfExists('transaction_reference');
            $table->dropColumnIfExists('deleted_at');
        });

        Schema::table('student_bill_payment_book', function (Blueprint $table) {
            $table->dropColumnIfExists('original_amount');
            $table->dropColumnIfExists('scholarship_deduction');
            $table->dropColumnIfExists('discount_deduction');
            $table->dropColumnIfExists('adjusted_amount');
        });

        Schema::table('student_bill_invoice', function (Blueprint $table) {
            $table->dropColumnIfExists('pdf_path');
            $table->dropColumnIfExists('amount');
            $table->dropColumnIfExists('deleted_at');
        });
    }
};
