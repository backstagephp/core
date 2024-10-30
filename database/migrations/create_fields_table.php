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
    }
};
