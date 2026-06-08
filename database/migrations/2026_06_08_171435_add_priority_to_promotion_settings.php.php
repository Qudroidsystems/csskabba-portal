<?php
// database/migrations/xxxx_add_priority_to_promotion_settings.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriorityToPromotionSettings extends Migration
{
    public function up()
    {
        Schema::table('promotion_settings', function (Blueprint $table) {
            // Add priority/rank field
            $table->integer('priority')->default(0)->after('term_id');

            // Remove the old unique constraint if it exists
            $table->dropUnique(['schoolclass_id', 'session_id', 'term_id']);

            // Add new unique constraint including priority
            $table->unique(['schoolclass_id', 'session_id', 'term_id', 'priority'], 'unique_promotion_priority');
        });
    }

    public function down()
    {
        Schema::table('promotion_settings', function (Blueprint $table) {
            $table->dropColumn('priority');
            $table->unique(['schoolclass_id', 'session_id', 'term_id']);
        });
    }
}
