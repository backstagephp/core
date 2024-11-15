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
        Schema::create('types', function (Blueprint $table) {
            $table->string('slug')->primary();

            $table->string('name');
            $table->string('name_plural');
            $table->string('title_field')->nullable();
            $table->string('body_field')->nullable();
            $table->boolean('public')->default(false);

            $table->timestamps();
        });
    }
};
