<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_overrides');
    }
};
