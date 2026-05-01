<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('id_card_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('front_html');
            $table->longText('back_html')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_card_templates');
    }
};
