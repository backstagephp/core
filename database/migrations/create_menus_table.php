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
        Schema::create('menus', function (Blueprint $table) {
            $table->string('slug')->primary();

            $table->string('name');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('menu_site', function (Blueprint $table) {
            $table->foreignUlid('site_ulid')->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('menu_slug');

            $table->foreign('menu_slug')->references('slug')->on('menus')->cascadeOnUpdate()->cascadeOnDelete();

            $table->index(['site_ulid', 'menu_slug']);
        });
    }
};
