<?php
// database/migrations/2026_04_24_000003_create_chart_of_accounts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chart_of_accounts')) {
            Schema::create('chart_of_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('account_code', 20)->unique();
                $table->string('account_name');
                $table->enum('account_type', ['asset', 'liability', 'equity', 'income', 'expense']);
                $table->enum('normal_balance', ['debit', 'credit']);
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('school_bill_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_bank_account')->default(false);
                $table->string('bank_name')->nullable();
                $table->string('bank_account_no')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();

                // Foreign keys
                $table->foreign('parent_id', 'fk_coa_parent')->references('id')->on('chart_of_accounts')->onDelete('cascade');
                $table->foreign('school_bill_id', 'fk_coa_bill')->references('id')->on('school_bill')->onDelete('set null');

                // Indexes
                $table->index('account_code');
                $table->index('account_type');
                $table->index('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
