<?php
// database/migrations/2026_04_24_000004_update_payment_gateways_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('payment_gateways', 'config')) {
                $table->json('config')->nullable()->after('mode');
            }
            if (!Schema::hasColumn('payment_gateways', 'fee_percentage')) {
                $table->decimal('fee_percentage', 5, 2)->default(0)->after('config');
            }
            if (!Schema::hasColumn('payment_gateways', 'fee_fixed')) {
                $table->decimal('fee_fixed', 15, 2)->default(0)->after('fee_percentage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->dropColumnIfExists('config');
            $table->dropColumnIfExists('fee_percentage');
            $table->dropColumnIfExists('fee_fixed');
        });
    }
};
