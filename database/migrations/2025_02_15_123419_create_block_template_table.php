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
        if (Schema::hasTable('block_template')) {
            return;
        }

        Schema::create('block_template', function (Blueprint $table) {
            $table->id();
            $table->string('block_slug');
            $table->string('template_slug');
            $table->integer('position')->default(0);

            // $table->foreign('block_slug')->references('slug')->on('blocks')->cascadeOnUpdate()->cascadeOnDelete();
            // $table->foreign('template_slug')->references('slug')->on('templates')->cascadeOnUpdate()->cascadeOnDelete();

            $table->index(['block_slug', 'template_slug']);
        });

        Schema::table('types', function (Blueprint $table) {
            $table->string('template_slug')->nullable();
            $table->foreign('template_slug')->references('slug')->on('templates')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
};
