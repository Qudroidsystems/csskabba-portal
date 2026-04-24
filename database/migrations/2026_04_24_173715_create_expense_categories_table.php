<?php
// database/migrations/2026_04_24_000002_create_expense_categories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('expense_categories')) {
            Schema::create('expense_categories', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('account_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                // Foreign key
                $table->foreign('account_id', 'fk_exp_cat_account')->references('id')->on('chart_of_accounts')->onDelete('set null');

                // Indexes
                $table->index('code');
                $table->index('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
