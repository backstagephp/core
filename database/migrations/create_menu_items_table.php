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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->ulid()->primary();

            $table->foreignUlid('site_ulid')->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('language_code', 2);
            $table->char('country_code', 2);

            $table->foreignUlid('parent_ulid')->nullable()->constrained(table: 'content', column: 'ulid')->cascadeOnUpdate()->nullOnDelete();

            $table->string('name');
            $table->string('slug');
            $table->string('title');
            $table->boolean('active');

            $table->string('url')->nullable();
            $table->string('target')->nullable();

            $table->unsignedInteger('position')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign(['language_code', 'country_code'])->references(['code', 'country_code'])->on('languages')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
};
