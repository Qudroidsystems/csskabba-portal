<?php
// database/migrations/2024_01_01_000001_fix_school_bill_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add missing columns to school_bill
        Schema::table('school_bill', function (Blueprint $table) {
            if (!Schema::hasColumn('school_bill', 'statusId')) {
                $table->unsignedBigInteger('statusId')->default(1)->after('bill_amount');
            }
            if (!Schema::hasColumn('school_bill', 'effective_from')) {
                $table->date('effective_from')->nullable()->after('statusId');
            }
            if (!Schema::hasColumn('school_bill', 'effective_to')) {
                $table->date('effective_to')->nullable()->after('effective_from');
            }
            if (!Schema::hasColumn('school_bill', 'is_mandatory')) {
                $table->boolean('is_mandatory')->default(true)->after('effective_to');
            }
            if (!Schema::hasColumn('school_bill', 'due_date')) {
                $table->date('due_date')->nullable()->after('is_mandatory');
            }
            if (!Schema::hasColumn('school_bill', 'late_fee')) {
                $table->decimal('late_fee', 15, 2)->default(0)->after('due_date');
            }
            if (!Schema::hasColumn('school_bill', 'late_fee_type')) {
                $table->enum('late_fee_type', ['fixed', 'percentage'])->default('fixed')->after('late_fee');
            }
            if (!Schema::hasColumn('school_bill', 'payment_frequency')) {
                $table->enum('payment_frequency', ['one_time', 'termly', 'monthly'])->default('one_time')->after('late_fee_type');
            }
            if (!Schema::hasColumn('school_bill', 'grace_period_days')) {
                $table->integer('grace_period_days')->default(0)->after('payment_frequency');
            }
            if (!Schema::hasColumn('school_bill', 'is_scholarship_eligible')) {
                $table->boolean('is_scholarship_eligible')->default(true)->after('grace_period_days');
            }
            if (!Schema::hasColumn('school_bill', 'is_discount_eligible')) {
                $table->boolean('is_discount_eligible')->default(true)->after('is_scholarship_eligible');
            }
            if (!Schema::hasColumn('school_bill', 'max_discount_percentage')) {
                $table->decimal('max_discount_percentage', 5, 2)->nullable()->after('is_discount_eligible');
            }
            if (!Schema::hasColumn('school_bill', 'category')) {
                $table->string('category')->nullable()->after('max_discount_percentage');
            }
            if (!Schema::hasColumn('school_bill', 'priority')) {
                $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->after('category');
            }
            if (!Schema::hasColumn('school_bill', 'attachment')) {
                $table->string('attachment')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('school_bill', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('attachment');
            }
            if (!Schema::hasColumn('school_bill', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // 2. Fix data types for school_bill_class_term_session
        Schema::table('school_bill_class_term_session', function (Blueprint $table) {
            // First, add new columns as unsignedBigInteger
            if (Schema::hasColumn('school_bill_class_term_session', 'bill_id') &&
                !Schema::hasColumn('school_bill_class_term_session', 'bill_id_int')) {
                $table->unsignedBigInteger('bill_id_int')->nullable()->after('id');
                $table->unsignedBigInteger('class_id_int')->nullable()->after('bill_id_int');
                $table->unsignedBigInteger('termid_id_int')->nullable()->after('class_id_int');
                $table->unsignedBigInteger('session_id_int')->nullable()->after('termid_id_int');
                $table->unsignedBigInteger('created_by_int')->nullable()->after('createdBy');
            }
        });

        // Migrate data from string to integer columns
        if (Schema::hasTable('school_bill') && Schema::hasTable('school_bill_class_term_session')) {
            DB::statement('UPDATE school_bill_class_term_session
                SET bill_id_int = CAST(bill_id AS UNSIGNED)
                WHERE bill_id IS NOT NULL AND bill_id != ""');

            DB::statement('UPDATE school_bill_class_term_session
                SET class_id_int = CAST(class_id AS UNSIGNED)
                WHERE class_id IS NOT NULL AND class_id != ""');

            DB::statement('UPDATE school_bill_class_term_session
                SET termid_id_int = CAST(termid_id AS UNSIGNED)
                WHERE termid_id IS NOT NULL AND termid_id != ""');

            DB::statement('UPDATE school_bill_class_term_session
                SET session_id_int = CAST(session_id AS UNSIGNED)
                WHERE session_id IS NOT NULL AND session_id != ""');

            DB::statement('UPDATE school_bill_class_term_session
                SET created_by_int = CAST(createdBy AS UNSIGNED)
                WHERE createdBy IS NOT NULL AND createdBy != ""');
        }

        // Drop old string columns and rename new ones
        Schema::table('school_bill_class_term_session', function (Blueprint $table) {
            if (Schema::hasColumn('school_bill_class_term_session', 'bill_id')) {
                $table->dropColumn('bill_id');
            }
            if (Schema::hasColumn('school_bill_class_term_session', 'class_id')) {
                $table->dropColumn('class_id');
            }
            if (Schema::hasColumn('school_bill_class_term_session', 'termid_id')) {
                $table->dropColumn('termid_id');
            }
            if (Schema::hasColumn('school_bill_class_term_session', 'session_id')) {
                $table->dropColumn('session_id');
            }
            if (Schema::hasColumn('school_bill_class_term_session', 'createdBy')) {
                $table->dropColumn('createdBy');
            }

            // Rename new columns
            if (Schema::hasColumn('school_bill_class_term_session', 'bill_id_int')) {
                $table->renameColumn('bill_id_int', 'bill_id');
            }
            if (Schema::hasColumn('school_bill_class_term_session', 'class_id_int')) {
                $table->renameColumn('class_id_int', 'class_id');
            }
            if (Schema::hasColumn('school_bill_class_term_session', 'termid_id_int')) {
                $table->renameColumn('termid_id_int', 'termid_id');
            }
            if (Schema::hasColumn('school_bill_class_term_session', 'session_id_int')) {
                $table->renameColumn('session_id_int', 'session_id');
            }
            if (Schema::hasColumn('school_bill_class_term_session', 'created_by_int')) {
                $table->renameColumn('created_by_int', 'created_by');
            }

            // Add foreign key constraints
            $table->foreign('bill_id')->references('id')->on('school_bill')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('schoolclass')->onDelete('cascade');
            $table->foreign('termid_id')->references('id')->on('schoolterm')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('schoolsession')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // Add unique constraint to prevent duplicates
            $table->unique(['bill_id', 'class_id', 'termid_id', 'session_id'], 'unique_bill_class_term_session');

            // Add indexes
            $table->index(['class_id', 'termid_id', 'session_id']);
            $table->index('bill_id');
        });
    }

    public function down(): void
    {
        // Rollback logic (preserves original structure)
        Schema::table('school_bill', function (Blueprint $table) {
            $table->dropColumnIfExists('statusId');
            $table->dropColumnIfExists('effective_from');
            $table->dropColumnIfExists('effective_to');
            $table->dropColumnIfExists('is_mandatory');
            $table->dropColumnIfExists('due_date');
            $table->dropColumnIfExists('late_fee');
            $table->dropColumnIfExists('late_fee_type');
            $table->dropColumnIfExists('payment_frequency');
            $table->dropColumnIfExists('grace_period_days');
            $table->dropColumnIfExists('is_scholarship_eligible');
            $table->dropColumnIfExists('is_discount_eligible');
            $table->dropColumnIfExists('max_discount_percentage');
            $table->dropColumnIfExists('category');
            $table->dropColumnIfExists('priority');
            $table->dropColumnIfExists('attachment');
            $table->dropColumnIfExists('is_active');
            $table->dropColumnIfExists('deleted_at');
        });
    }
};
