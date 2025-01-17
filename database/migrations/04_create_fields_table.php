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
        Schema::create('fields', function (Blueprint $table) {
            $table->ulid('ulid')->primary();
            $table->ulid('parent_ulid')->nullable();

            $table->string('model_type');
            $table->string('model_key');

            $table->string('slug');

            $table->string('name');
            $table->string('field_type');
            $table->json('config')->nullable();

            $table->unsignedInteger('position')->default(0);

            $table->timestamps();

            $table->unique(['model_type', 'model_key', 'slug']);
        });

        Schema::create('field_site', function (Blueprint $table) {
            $table->foreignUlid('site_ulid')->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('field_ulid')->constrained(table: 'fields', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();

            $table->index(['site_ulid', 'field_ulid']);
        });
    }
};
