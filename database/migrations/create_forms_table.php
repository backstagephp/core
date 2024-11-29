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
            $table->string('name_field')->nullable();
            $table->string('submit_button')->nullable();

            $table->timestamps();
        });

        Schema::create('form_site', function (Blueprint $table) {
            $table->foreignUlid('site_ulid')->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('form_slug');

            $table->foreign('form_slug')->references('slug')->on('forms')->cascadeOnUpdate()->cascadeOnDelete();

            $table->index(['site_ulid', 'form_slug']);
        });
    }
};
