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

            $table->string('model_type');
            $table->string('model_slug');

            $table->string('slug');

            $table->string('name');
            $table->string('field_type');
            $table->json('config')->nullable();

            $table->unsignedInteger('position')->default(0);

            $table->timestamps();

            $table->unique(['model_type', 'model_slug', 'slug']);
        });

        Schema::create('fieldables', function (Blueprint $table) {
            $table->foreignUlid('field_ulid')->constrained(table: 'fields', column: 'ulid')->cascadeOnDelete();

            $table->string('fieldable_type');
            $table->ulid('fieldable_ulid');

            $table->index(['fieldable_type', 'fieldable_ulid']);
            $table->unique(['field_ulid', 'fieldable_ulid', 'fieldable_type']);
        });
    }
};
