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
        Schema::create('domains', function (Blueprint $table) {
            $table->ulid()->primary();
            $table->foreignUlid('site_ulid')->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->ulid('alias_of')->nullable()->index();
            $table->char('language_code', 2)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->string('environment')->nullable();
            $table->timestamps();

            $table->foreign(['language_code', 'country_code'])->references(['code', 'country_code'])->on('languages')->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->foreign('alias_of')->references('ulid')->on('domains')->cascadeOnUpdate()->nullOnDelete();
        });
    }
};
