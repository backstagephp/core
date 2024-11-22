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
        Schema::create('content', function (Blueprint $table) {
            $table->ulid()->primary();
            $table->foreignUlid('site_ulid')->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('language_code', 2);
            $table->char('country_code', 2)->nullable();
            $table->string('type_slug');
            $table->foreignId('author_id')->nullable()->constrained(table: 'users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUlid('parent_ulid')->nullable()->constrained(table: 'content', column: 'ulid')->cascadeOnUpdate()->nullOnDelete();
            $table->string('title');
            $table->string('name');
            $table->string('slug');
            $table->string('path')->nullable();
            $table->json('meta')->nullable();
            $table->json('microdata')->nullable();
            $table->string('template')->nullable();
            $table->string('password')->nullable();
            $table->boolean('auth')->default(false);
            $table->boolean('cache')->default(true);
            $table->boolean('public')->default(true);
            $table->boolean('pin')->default(false);
            $table->boolean('lock')->default(false);
            $table->timestamps();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('disapproved_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('pinned_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('refreshed_at')->nullable();
            $table->timestamp('searchable_at')->nullable();
            $table->softDeletes();

            $table->foreign('type_slug')->references('slug')->on('types')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign(['language_code', 'country_code'])->references(['code', 'country_code'])->on('languages')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
};
