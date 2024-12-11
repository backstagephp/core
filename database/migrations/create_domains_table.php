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
            $table->char('language_code', 5)->nullable();
            $table->string('name');
            $table->ulid('alias_of')->nullable()->index();
            $table->string('environment')->nullable();
            $table->timestamps();

            $table->foreign('language_code')->references('code')->on('languages')->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->foreign('alias_of')->references('ulid')->on('domains')->cascadeOnUpdate()->nullOnDelete();
        });
    }
};
