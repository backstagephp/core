<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->char('code', 2);
            $table->char('country_code', 2);
            $table->string('hreflang')->nullable();
            $table->string('name');
            $table->string('native');
            $table->boolean('active');
            $table->boolean('default');
            $table->timestamps();

            $table->primary(['code', 'country_code']);
            $table->unique(['code', 'country_code']);
        });
    }
};
