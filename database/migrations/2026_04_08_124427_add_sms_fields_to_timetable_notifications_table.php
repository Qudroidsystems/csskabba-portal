<?php
// database/migrations/2025_01_15_000008_add_sms_fields_to_notifications.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('timetable_notifications', function (Blueprint $table) {
            $table->string('phone_number', 20)->nullable()->after('email');
            $table->enum('channel', ['email', 'sms', 'both'])->default('email')->after('status');
            $table->timestamp('sms_sent_at')->nullable()->after('sent_at');
        });


    }

    public function down()
    {
       
        Schema::table('timetable_notifications', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'channel', 'sms_sent_at']);
        });
    }
};
