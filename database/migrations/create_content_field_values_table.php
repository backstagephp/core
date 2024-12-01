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
        Schema::create('content_field_values', function (Blueprint $table) {
            $table->ulid()->primary();

            $table->foreignUlid('content_ulid')->nullable()->constrained(table: 'content', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('field_ulid')->nullable()->constrained(table: 'fields', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();

            $table->longText('value');

            $table->timestamps();
            $table->softDeletes();
        });
    }
};
