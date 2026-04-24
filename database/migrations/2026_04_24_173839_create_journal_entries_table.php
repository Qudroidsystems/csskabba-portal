<?php
// database/migrations/2026_04_24_000005_create_journal_entries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('journal_entries')) {
            Schema::create('journal_entries', function (Blueprint $table) {
                $table->id();
                $table->string('entry_no', 50)->unique();
                $table->date('entry_date');
                $table->enum('entry_type', ['payment', 'receipt', 'contra', 'journal', 'payroll', 'adjustment', 'reversal', 'petty_cash']);
                $table->text('description');
                $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft');
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('reversal_reason')->nullable();
                $table->unsignedBigInteger('reversed_by')->nullable();
                $table->timestamp('reversed_at')->nullable();
                $table->timestamps();

                // Foreign keys
                $table->foreign('created_by', 'fk_je_created')->references('id')->on('users');
                $table->foreign('approved_by', 'fk_je_approved')->references('id')->on('users');
                $table->foreign('reversed_by', 'fk_je_reversed')->references('id')->on('users');

                // Indexes
                $table->index(['entry_date', 'status'], 'idx_je_date_status');
                $table->index('entry_no');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
