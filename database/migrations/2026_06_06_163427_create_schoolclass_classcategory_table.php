<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_schoolclass_classcategory_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolclassClasscategoryTable extends Migration
{
    public function up()
    {
        Schema::create('schoolclass_classcategory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schoolclass_id')->constrained('schoolclass')->onDelete('cascade');
            $table->foreignId('classcategory_id')->constrained('classcategories')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['schoolclass_id', 'classcategory_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('schoolclass_classcategory');
    }
}
