<?php
// database/migrations/2026_04_23_183617_fix_school_bill_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================
        // PART 1: Add missing columns to school_bill
        // ============================================
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

        // ============================================
        // PART 2: Add missing columns to school_bill_class_term_session
        // ============================================

        // First, check if createdBy column exists, if not, add it
        if (!Schema::hasColumn('school_bill_class_term_session', 'createdBy') && !Schema::hasColumn('school_bill_class_term_session', 'created_by')) {
            Schema::table('school_bill_class_term_session', function (Blueprint $table) {
                $table->string('createdBy')->nullable()->after('session_id');
            });
        }

        // Add created_by column if needed (rename from createdBy if exists)
        if (Schema::hasColumn('school_bill_class_term_session', 'createdBy') && !Schema::hasColumn('school_bill_class_term_session', 'created_by')) {
            Schema::table('school_bill_class_term_session', function (Blueprint $table) {
                $table->renameColumn('createdBy', 'created_by');
            });
        }

        if (!Schema::hasColumn('school_bill_class_term_session', 'created_by')) {
            Schema::table('school_bill_class_term_session', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->after('session_id');
            });
        }

        // Convert string IDs to integers if needed - using a safer approach
        // Check if columns exist and are not already integers
        $columnsToCheck = ['bill_id', 'class_id', 'termid_id', 'session_id'];
        $needsConversion = false;

        foreach ($columnsToCheck as $column) {
            if (Schema::hasColumn('school_bill_class_term_session', $column)) {
                try {
                    $columnType = DB::getSchemaBuilder()->getColumnType('school_bill_class_term_session', $column);
                    if ($columnType !== 'bigint' && $columnType !== 'integer' && $columnType !== 'bigint unsigned') {
                        $needsConversion = true;
                        break;
                    }
                } catch (\Exception $e) {
                    $needsConversion = true;
                }
            }
        }

        if ($needsConversion) {
            // Add new integer columns
            Schema::table('school_bill_class_term_session', function (Blueprint $table) {
                if (!Schema::hasColumn('school_bill_class_term_session', 'bill_id_new')) {
                    $table->unsignedBigInteger('bill_id_new')->nullable()->after('id');
                    $table->unsignedBigInteger('class_id_new')->nullable()->after('bill_id_new');
                    $table->unsignedBigInteger('termid_id_new')->nullable()->after('class_id_new');
                    $table->unsignedBigInteger('session_id_new')->nullable()->after('termid_id_new');
                }
            });

            // Migrate data
            DB::statement('UPDATE school_bill_class_term_session SET bill_id_new = CAST(bill_id AS UNSIGNED) WHERE bill_id IS NOT NULL AND bill_id != ""');
            DB::statement('UPDATE school_bill_class_term_session SET class_id_new = CAST(class_id AS UNSIGNED) WHERE class_id IS NOT NULL AND class_id != ""');
            DB::statement('UPDATE school_bill_class_term_session SET termid_id_new = CAST(termid_id AS UNSIGNED) WHERE termid_id IS NOT NULL AND termid_id != ""');
            DB::statement('UPDATE school_bill_class_term_session SET session_id_new = CAST(session_id AS UNSIGNED) WHERE session_id IS NOT NULL AND session_id != ""');

            // Drop old columns and rename new ones
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

                if (Schema::hasColumn('school_bill_class_term_session', 'bill_id_new')) {
                    $table->renameColumn('bill_id_new', 'bill_id');
                    $table->renameColumn('class_id_new', 'class_id');
                    $table->renameColumn('termid_id_new', 'termid_id');
                    $table->renameColumn('session_id_new', 'session_id');
                }
            });
        }

        // ============================================
        // PART 3: Add Foreign Keys and Indexes (only if they don't exist)
        // ============================================

        // Check if foreign keys exist before adding them
        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                                   WHERE TABLE_SCHEMA = DATABASE()
                                   AND TABLE_NAME = 'school_bill_class_term_session'
                                   AND CONSTRAINT_NAME LIKE 'fk_%'");

        $existingForeignKeys = array_column($foreignKeys, 'CONSTRAINT_NAME');

        Schema::table('school_bill_class_term_session', function (Blueprint $table) use ($existingForeignKeys) {
            if (Schema::hasColumn('school_bill_class_term_session', 'bill_id') && !in_array('fk_sbcts_bill', $existingForeignKeys)) {
                $table->foreign('bill_id', 'fk_sbcts_bill')->references('id')->on('school_bill')->onDelete('cascade');
            }
            if (Schema::hasColumn('school_bill_class_term_session', 'class_id') && !in_array('fk_sbcts_class', $existingForeignKeys)) {
                $table->foreign('class_id', 'fk_sbcts_class')->references('id')->on('schoolclass')->onDelete('cascade');
            }
            if (Schema::hasColumn('school_bill_class_term_session', 'termid_id') && !in_array('fk_sbcts_term', $existingForeignKeys)) {
                $table->foreign('termid_id', 'fk_sbcts_term')->references('id')->on('schoolterm')->onDelete('cascade');
            }
            if (Schema::hasColumn('school_bill_class_term_session', 'session_id') && !in_array('fk_sbcts_session', $existingForeignKeys)) {
                $table->foreign('session_id', 'fk_sbcts_session')->references('id')->on('schoolsession')->onDelete('cascade');
            }
            if (Schema::hasColumn('school_bill_class_term_session', 'created_by') && !in_array('fk_sbcts_created', $existingForeignKeys)) {
                $table->foreign('created_by', 'fk_sbcts_created')->references('id')->on('users')->onDelete('cascade');
            }

            // Add unique constraint
            try {
                $table->unique(['bill_id', 'class_id', 'termid_id', 'session_id'], 'uk_sbcts_unique');
            } catch (\Exception $e) {
                // Constraint might already exist
            }

            // Add indexes
            try {
                $table->index(['class_id', 'termid_id', 'session_id'], 'idx_sbcts_cts');
            } catch (\Exception $e) {
                // Index might already exist
            }
            try {
                $table->index('bill_id', 'idx_sbcts_bill');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });
    }

    public function down(): void
    {
        // Drop foreign keys if they exist
        Schema::table('school_bill_class_term_session', function (Blueprint $table) {
            try {
                $table->dropForeign('fk_sbcts_bill');
            } catch (\Exception $e) {}
            try {
                $table->dropForeign('fk_sbcts_class');
            } catch (\Exception $e) {}
            try {
                $table->dropForeign('fk_sbcts_term');
            } catch (\Exception $e) {}
            try {
                $table->dropForeign('fk_sbcts_session');
            } catch (\Exception $e) {}
            try {
                $table->dropForeign('fk_sbcts_created');
            } catch (\Exception $e) {}

            try {
                $table->dropUnique('uk_sbcts_unique');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('idx_sbcts_cts');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('idx_sbcts_bill');
            } catch (\Exception $e) {}
        });

        // Drop added columns from school_bill
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
