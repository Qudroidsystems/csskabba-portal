<?php
// database/migrations/2026_05_25_000001_add_audit_and_lock_fields_to_broadsheets.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('broadsheets', function (Blueprint $table) {
            // Audit trail fields
            if (!Schema::hasColumn('broadsheets', 'entered_by')) {
                $table->unsignedBigInteger('entered_by')->nullable()->after('vettedstatus');
            }
            if (!Schema::hasColumn('broadsheets', 'entered_at')) {
                $table->timestamp('entered_at')->nullable()->after('entered_by');
            }
            if (!Schema::hasColumn('broadsheets', 'last_modified_by')) {
                $table->unsignedBigInteger('last_modified_by')->nullable()->after('entered_at');
            }
            if (!Schema::hasColumn('broadsheets', 'last_modified_at')) {
                $table->timestamp('last_modified_at')->nullable()->after('last_modified_by');
            }
            if (!Schema::hasColumn('broadsheets', 'entry_source')) {
                $table->string('entry_source')->nullable()->default('teacher')->comment('teacher or admin')->after('last_modified_at');
            }

            // Lock fields
            if (!Schema::hasColumn('broadsheets', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('entry_source');
            }
            if (!Schema::hasColumn('broadsheets', 'locked_by')) {
                $table->unsignedBigInteger('locked_by')->nullable()->after('is_locked');
            }
            if (!Schema::hasColumn('broadsheets', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('locked_by');
            }
            if (!Schema::hasColumn('broadsheets', 'lock_reason')) {
                $table->text('lock_reason')->nullable()->after('locked_at');
            }
        });

        // Add foreign keys
        Schema::table('broadsheets', function (Blueprint $table) {
            $foreignKeys = collect(Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('broadsheets'));

            $hasEnteredByFk = $foreignKeys->contains(function($fk) {
                return $fk->getLocalColumns() === ['entered_by'];
            });
            if (!$hasEnteredByFk && Schema::hasColumn('broadsheets', 'entered_by')) {
                $table->foreign('entered_by')->references('id')->on('users')->onDelete('set null');
            }

            $hasModifiedByFk = $foreignKeys->contains(function($fk) {
                return $fk->getLocalColumns() === ['last_modified_by'];
            });
            if (!$hasModifiedByFk && Schema::hasColumn('broadsheets', 'last_modified_by')) {
                $table->foreign('last_modified_by')->references('id')->on('users')->onDelete('set null');
            }

            $hasLockedByFk = $foreignKeys->contains(function($fk) {
                return $fk->getLocalColumns() === ['locked_by'];
            });
            if (!$hasLockedByFk && Schema::hasColumn('broadsheets', 'locked_by')) {
                $table->foreign('locked_by')->references('id')->on('users')->onDelete('set null');
            }
        });

        // Add indexes for performance
        Schema::table('broadsheets', function (Blueprint $table) {
            if (!Schema::hasIndex('broadsheets', 'broadsheets_lock_index')) {
                $table->index(['is_locked', 'term_id', 'session_id'], 'broadsheets_lock_index');
            }
            if (!Schema::hasIndex('broadsheets', 'broadsheets_entered_at_index')) {
                $table->index('entered_at', 'broadsheets_entered_at_index');
            }
            if (!Schema::hasIndex('broadsheets', 'broadsheets_modified_at_index')) {
                $table->index('last_modified_at', 'broadsheets_modified_at_index');
            }
        });
    }

    public function down()
    {
        Schema::table('broadsheets', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['entered_by']);
            $table->dropForeign(['last_modified_by']);
            $table->dropForeign(['locked_by']);

            // Drop indexes
            $table->dropIndex('broadsheets_lock_index');
            $table->dropIndex('broadsheets_entered_at_index');
            $table->dropIndex('broadsheets_modified_at_index');

            // Drop columns
            $table->dropColumn([
                'entered_by', 'entered_at', 'last_modified_by', 'last_modified_at',
                'entry_source', 'is_locked', 'locked_by', 'locked_at', 'lock_reason'
            ]);
        });
    }
};
