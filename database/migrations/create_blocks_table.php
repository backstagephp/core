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
        Schema::create('blocks', function (Blueprint $table) {
            $table->string('slug')->primary();

            $table->string('name');
            $table->string('name_field')->nullable();
            $table->string('icon');
            $table->string('component')->nullable();

            $table->timestamps();
        });

        Schema::create('block_site', function (Blueprint $table) {
            $table->foreignUlid('site_ulid')->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('block_slug');

            $table->foreign('block_slug')->references('slug')->on('blocks')->cascadeOnUpdate()->cascadeOnDelete();

            $table->index(['site_ulid', 'block_slug']);
        });
    }
};
