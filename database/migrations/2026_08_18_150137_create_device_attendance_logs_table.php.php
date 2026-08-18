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
        Schema::create('device_attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_serial');
            $table->unsignedInteger('device_pin');
            $table->dateTime('punch_time');
            $table->unsignedTinyInteger('verify_mode')->nullable();  // 1=fingerprint,4=card etc (pyzk gives this)
            $table->unsignedTinyInteger('status_code')->nullable();  // pyzk 'status' field (raw)
            $table->enum('processing_status', ['pending', 'processed', 'unmapped', 'error'])->default('pending');
            $table->string('process_note')->nullable();
            $table->timestamps();

            $table->unique(['device_serial', 'device_pin', 'punch_time'], 'uniq_device_punch');
            $table->index('processing_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_attendance_logs');
    }
};
