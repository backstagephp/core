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
        Schema::create('forms', function (Blueprint $table) {
            $table->string('slug')->primary();

            $table->string('name');
            $table->string('title_field')->nullable();
            $table->string('submit_button')->nullable();

            $table->timestamps();
        });
    }
};
