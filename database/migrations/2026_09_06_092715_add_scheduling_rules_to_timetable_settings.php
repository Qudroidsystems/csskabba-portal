<?php
// database/migrations/2026_09_06_000003_add_scheduling_rules_to_timetable_settings.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetable_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('lessons_per_day')->nullable()->after('max_lessons_per_day');
            $table->unsignedTinyInteger('short_break_after_period')->nullable()->after('lessons_per_day');
            $table->unsignedTinyInteger('long_break_after_period')->nullable()->after('short_break_after_period');
            $table->string('assembly_day')->nullable()->after('long_break_after_period');
            $table->json('half_days')->nullable()->after('assembly_day'); // {"Friday": 5} = Friday has only 5 teaching slots
            $table->boolean('deprioritize_break_adjacent')->default(true)->after('half_days');
        });
    }

    public function down(): void
    {
        Schema::table('timetable_settings', function (Blueprint $table) {
            $table->dropColumn(['lessons_per_day','short_break_after_period','long_break_after_period','assembly_day','half_days','deprioritize_break_adjacent']);
        });
    }
};