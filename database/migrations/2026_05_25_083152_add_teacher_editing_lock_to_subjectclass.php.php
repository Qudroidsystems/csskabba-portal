<?php
// database/migrations/2026_05_25_000003_add_teacher_editing_lock_to_subjectclass.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('subjectclass', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('subjectclass', 'teacher_editing_enabled')) {
                $table->boolean('teacher_editing_enabled')->default(true)->after('status');
            }
            if (!Schema::hasColumn('subjectclass', 'teacher_editing_disabled_at')) {
                $table->timestamp('teacher_editing_disabled_at')->nullable()->after('teacher_editing_enabled');
            }
            if (!Schema::hasColumn('subjectclass', 'teacher_editing_disabled_by')) {
                $table->unsignedBigInteger('teacher_editing_disabled_by')->nullable()->after('teacher_editing_disabled_at');
            }
        });

        // Add foreign key if it doesn't exist
        try {
            Schema::table('subjectclass', function (Blueprint $table) {
                if (Schema::hasColumn('subjectclass', 'teacher_editing_disabled_by')) {
                    $table->foreign('teacher_editing_disabled_by')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null');
                }
            });
        } catch (\Exception $e) {
            // Foreign key might already exist
        }
    }

    public function down()
    {
        Schema::table('subjectclass', function (Blueprint $table) {
            // Drop foreign key first
            try {
                $table->dropForeign(['teacher_editing_disabled_by']);
            } catch (\Exception $e) {
                // Foreign key might not exist
            }

            // Drop columns
            $table->dropColumn([
                'teacher_editing_enabled',
                'teacher_editing_disabled_at',
                'teacher_editing_disabled_by',
            ]);
        });
    }
};
