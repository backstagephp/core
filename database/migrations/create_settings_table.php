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
        Schema::create('settings', function (Blueprint $table) {
            $table->string('slug')->primary();
            $table->foreignUlid('site_ulid')->nullable()->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained(table: 'users')->cascadeOnUpdate()->nullOnDelete();
            $table->char('language_code', 2)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign(['language_code', 'country_code'])->references(['code', 'country_code'])->on('languages')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
};
