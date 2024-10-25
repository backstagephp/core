<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->json('config');

            $table->timestamps();

            $table->unique(['model_type', 'model_slug', 'slug']);
        });
    }
};
