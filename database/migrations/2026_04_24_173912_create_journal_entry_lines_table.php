<?php
// database/migrations/2026_04_24_000006_create_journal_entry_lines_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('journal_entry_lines')) {
            Schema::create('journal_entry_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('journal_entry_id');
                $table->unsignedBigInteger('account_id');
                $table->decimal('debit', 15, 2)->default(0);
                $table->decimal('credit', 15, 2)->default(0);
                $table->text('narration')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->unsignedBigInteger('staff_id')->nullable();
                $table->unsignedBigInteger('expense_id')->nullable();
                $table->timestamps();

                // Foreign keys
                $table->foreign('journal_entry_id', 'fk_jel_entry')->references('id')->on('journal_entries')->onDelete('cascade');
                $table->foreign('account_id', 'fk_jel_account')->references('id')->on('chart_of_accounts');
                $table->foreign('student_id', 'fk_jel_student')->references('id')->on('studentRegistration')->onDelete('set null');
                $table->foreign('staff_id', 'fk_jel_staff')->references('id')->on('staff_records')->onDelete('set null');

                // Indexes
                $table->index(['journal_entry_id', 'account_id'], 'idx_jel_entry_account');
                $table->index('debit');
                $table->index('credit');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};
