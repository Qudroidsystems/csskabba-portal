<?php
// database/migrations/2025_01_15_000007_create_holidays_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('type', ['public_holiday', 'school_holiday', 'exam_period', 'special_event']);
            $table->boolean('affects_timetable')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Holiday timetable overrides
        Schema::create('timetable_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('setting_id');
            $table->date('override_date');
            $table->json('modified_slots')->nullable(); // Store modified schedule for that day
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('setting_id')->references('id')->on('timetable_settings')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('timetable_overrides');
        Schema::dropIfExists('holidays');
    }
};
