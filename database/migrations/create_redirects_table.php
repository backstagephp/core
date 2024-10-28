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
        Schema::create('redirects', function (Blueprint $table) {
            $table->ulid()->primary();
            $table->foreignUlid('site_ulid')->nullable()->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('language_code', 2)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->string('path');
            $table->foreignUlid('content_ulid')->nullable()->constrained(table: 'content', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('destination')->nullable();
            $table->unsignedInteger('code', 3)->default(301)->autoIncrement(false);
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamps();

            $table->foreign(['language_code', 'country_code'])->references(['code', 'country_code'])->on('languages')->cascadeOnUpdate()->nullOnDelete();
        });
    }
};
