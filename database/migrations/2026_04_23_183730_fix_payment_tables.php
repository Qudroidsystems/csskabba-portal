<?php
// database/migrations/2024_01_01_000002_fix_payment_tables.php

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

        // Fix data types for foreign keys in student_bill_payment
        $this->fixForeignKeyColumns('student_bill_payment', [
            'student_id', 'school_bill_id', 'class_id', 'termid_id', 'session_id', 'generated_by'
        ]);

        // Add foreign key constraints to student_bill_payment
        Schema::table('student_bill_payment', function (Blueprint $table) {
            if (!Schema::hasColumn('student_bill_payment', 'student_id_int')) {
                // Add foreign keys after data type fix
                $table->foreign('student_id')->references('id')->on('studentRegistration')->onDelete('cascade');
                $table->foreign('school_bill_id')->references('id')->on('school_bill')->onDelete('cascade');
                $table->foreign('class_id')->references('id')->on('schoolclass');
                $table->foreign('termid_id')->references('id')->on('schoolterm');
                $table->foreign('session_id')->references('id')->on('schoolsession');
                $table->foreign('generated_by')->references('id')->on('users');

                // Add indexes
                $table->index(['student_id', 'termid_id', 'session_id']);
                $table->index('payment_status');
            }
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
            if (!Schema::hasColumn('student_bill_payment_record', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }

            // Fix data types
            $table->unsignedBigInteger('student_bill_payment_id')->change();
            $table->foreign('student_bill_payment_id')->references('id')->on('student_bill_payment')->onDelete('cascade');
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
            // Fix data types for foreign keys
            $this->fixForeignKeyColumns('student_bill_invoice', [
                'student_id', 'school_bill_id', 'class_id', 'termid_id', 'session_id', 'generated_by'
            ]);

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

    private function fixForeignKeyColumns($table, $columns): void
    {
        Schema::table($table, function (Blueprint $table) use ($columns) {
            // This is a helper - actual implementation uses raw SQL for data conversion
        });

        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                // Convert string to unsigned big integer
                DB::statement("ALTER TABLE {$table} MODIFY {$column} BIGINT UNSIGNED NOT NULL");
            }
        }
    }

    public function down(): void
    {
        // Remove foreign keys and restore original structure
        Schema::table('student_bill_payment', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['school_bill_id']);
            $table->dropForeign(['class_id']);
            $table->dropForeign(['termid_id']);
            $table->dropForeign(['session_id']);
            $table->dropForeign(['generated_by']);

            $table->dropColumnIfExists('total_paid');
            $table->dropColumnIfExists('total_balance');
            $table->dropColumnIfExists('last_payment_date');
            $table->dropColumnIfExists('payment_status');
            $table->dropColumnIfExists('session_token');
            $table->dropColumnIfExists('deleted_at');
        });
    }
};
