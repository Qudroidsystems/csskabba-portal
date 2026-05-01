<?php
// database/migrations/2024_01_01_000000_create_password_reset_histories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePasswordResetHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('password_reset_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('action'); // created, reset, revoked, printed
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->text('old_password_hash')->nullable();
            $table->text('new_password_hash')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('studentRegistration')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['student_id', 'action']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('password_reset_histories');
    }
}
