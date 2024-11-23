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
        Schema::create('form_actions', function (Blueprint $table) {
            $table->ulid()->primary();

            $table->string('form_slug')->constrained(table: 'forms', column: 'slug')->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignUlid('site_ulid')->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('language_code', 2);
            $table->char('country_code', 2);

            $table->string('type')->nullable();
            $table->json('config')->nullable();

            $table->timestamps();
        });
    }
};
