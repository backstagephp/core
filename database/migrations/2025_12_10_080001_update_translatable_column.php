<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('translated_attributes', function (Blueprint $table) {
            $table->string('translatable_id', 36)->change();
            $table->string('translatable_type', 36)->change();
        });
    }

    public function down()
    {
        Schema::dropIfExists('translated_attributes');
    }
};
