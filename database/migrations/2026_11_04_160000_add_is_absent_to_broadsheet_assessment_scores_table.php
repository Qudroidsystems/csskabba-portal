<?php
// Records that a student was ABSENT for an assessment, so scoresheets and
// reports can tell "absent" apart from a genuine score of 0. The score itself
// stays 0 and counts as 0 in every total.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('broadsheet_assessment_scores', 'is_absent')) {
            Schema::table('broadsheet_assessment_scores', function (Blueprint $table) {
                $table->boolean('is_absent')->default(false)->after('score');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('broadsheet_assessment_scores', 'is_absent')) {
            Schema::table('broadsheet_assessment_scores', function (Blueprint $table) {
                $table->dropColumn('is_absent');
            });
        }
    }
};
