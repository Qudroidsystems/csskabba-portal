<?php
// database/migrations/2025_01_01_000001_create_timetable_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Global timetable settings per class ────────────────────────────
        Schema::create('timetable_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schoolclass_id');
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('term_id')->nullable();

            $table->time('school_day_start')->default('08:00:00');
            $table->time('school_day_end')->default('14:30:00');
            $table->unsignedSmallInteger('period_duration_minutes')->default(40);
            $table->unsignedSmallInteger('short_break_duration_minutes')->default(20);
            $table->unsignedSmallInteger('long_break_duration_minutes')->default(40);
            $table->boolean('is_active')->default(true);
            $table->json('active_days')->default('["Monday","Tuesday","Wednesday","Thursday","Friday"]');
            $table->timestamps();

            $table->index(['schoolclass_id', 'session_id', 'term_id']);
            $table->foreign('schoolclass_id')->references('id')->on('schoolclass')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('schoolsession')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('schoolterm')->onDelete('cascade');
        });












    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_settings');
    }
};
