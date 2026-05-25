<?php
// database/migrations/2026_05_25_000002_create_scoresheet_locks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('scoresheet_locks')) {
            Schema::create('scoresheet_locks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('subjectclass_id');
                $table->unsignedBigInteger('term_id');
                $table->unsignedBigInteger('session_id');
                $table->unsignedBigInteger('locked_by');
                $table->timestamp('locked_at');
                $table->boolean('is_active')->default(true);
                $table->text('reason')->nullable();
                $table->timestamps();

                $table->foreign('subjectclass_id')->references('id')->on('subjectclass')->onDelete('cascade');
                $table->foreign('term_id')->references('id')->on('schoolterm')->onDelete('cascade');
                $table->foreign('session_id')->references('id')->on('schoolsession')->onDelete('cascade');
                $table->foreign('locked_by')->references('id')->on('users')->onDelete('cascade');

                $table->unique(['subjectclass_id', 'term_id', 'session_id'], 'unique_scoresheet_lock');
                $table->index('is_active');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('scoresheet_locks');
    }
};
