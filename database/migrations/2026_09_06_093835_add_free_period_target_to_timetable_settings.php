<?php
// database/migrations/2026_09_06_000002_add_free_period_target_to_timetable_settings.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetable_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('free_periods_per_week')->nullable()->after('active_days');
            $table->unsignedSmallInteger('max_lessons_per_day')->nullable()->after('free_periods_per_week');
        });
    }

    public function down(): void
    {
        Schema::table('timetable_settings', function (Blueprint $table) {
            $table->dropColumn(['free_periods_per_week', 'max_lessons_per_day']);
        });
    }
};