<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Soft-deleted bill assignments still count against the unique key
 * (bill, class, term, session), so a bill that was once assigned and then
 * deleted could never be assigned to that class/term again. Nothing reads
 * these dead rows, so remove them.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('school_bill_class_term_session')
            && Schema::hasColumn('school_bill_class_term_session', 'deleted_at')) {
            DB::table('school_bill_class_term_session')->whereNotNull('deleted_at')->delete();
        }
    }

    public function down(): void
    {
        // Deleted rows cannot be restored.
    }
};
