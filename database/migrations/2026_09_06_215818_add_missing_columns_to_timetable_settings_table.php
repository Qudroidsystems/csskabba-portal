<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetable_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('timetable_settings', 'free_periods_per_week')) {
                $table->unsignedInteger('free_periods_per_week')->nullable()->default(0);
            }
            if (!Schema::hasColumn('timetable_settings', 'max_lessons_per_day')) {
                $table->unsignedInteger('max_lessons_per_day')->nullable();
            }
            if (!Schema::hasColumn('timetable_settings', 'lessons_per_day')) {
                $table->unsignedInteger('lessons_per_day')->nullable();
            }
            if (!Schema::hasColumn('timetable_settings', 'short_break_after_period')) {
                $table->unsignedInteger('short_break_after_period')->nullable();
            }
            if (!Schema::hasColumn('timetable_settings', 'long_break_after_period')) {
                $table->unsignedInteger('long_break_after_period')->nullable();
            }
            if (!Schema::hasColumn('timetable_settings', 'assembly_day')) {
                $table->string('assembly_day')->nullable();
            }
            if (!Schema::hasColumn('timetable_settings', 'half_days')) {
                $table->json('half_days')->nullable();
            }
            if (!Schema::hasColumn('timetable_settings', 'deprioritize_break_adjacent')) {
                $table->boolean('deprioritize_break_adjacent')->default(true);
            }
            if (!Schema::hasColumn('timetable_settings', 'editing_by')) {
                $table->unsignedBigInteger('editing_by')->nullable();
            }
            if (!Schema::hasColumn('timetable_settings', 'editing_at')) {
                $table->timestamp('editing_at')->nullable();
            }
        });

        if (!Schema::hasColumn('timetable_settings', 'editing_by')) {
            return; // column already existed from a prior partial run; skip FK add below in that case
        }

        // Add the FK separately — adding it inside the same Blueprint as a
        // conditional column can fail on some MySQL versions if the column
        // already existed without an index.
        Schema::table('timetable_settings', function (Blueprint $table) {
            $fkExists = collect(\DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'timetable_settings'
                AND COLUMN_NAME = 'editing_by'
                AND REFERENCED_TABLE_NAME = 'users'
            "))->isNotEmpty();

            if (!$fkExists) {
                $table->foreign('editing_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('timetable_settings', function (Blueprint $table) {
            foreach ([
                'free_periods_per_week', 'max_lessons_per_day', 'lessons_per_day',
                'short_break_after_period', 'long_break_after_period', 'assembly_day',
                'half_days', 'deprioritize_break_adjacent', 'editing_at',
            ] as $col) {
                if (Schema::hasColumn('timetable_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('timetable_settings', 'editing_by')) {
                $table->dropForeign(['editing_by']);
                $table->dropColumn('editing_by');
            }
        });
    }
};