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
        Schema::create('templates', function (Blueprint $table) {
            $table->string('slug')->primary();

            $table->string('name');

            $table->timestamps();
        });

        Schema::create('template_site', function (Blueprint $table) {
            $table->foreignUlid('site_ulid')->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('template_slug');

            $table->foreign('template_slug')->references('slug')->on('templates')->cascadeOnUpdate()->cascadeOnDelete();

            $table->index(['site_ulid', 'template_slug']);
        });
    }
};
