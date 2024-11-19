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
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->ulid()->primary();

            $table->string('form_slug')->constrained(table: 'forms', column: 'slug')->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignUlid('site_ulid')->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('language_code', 2);
            $table->char('country_code', 2);

            $table->foreignUlid('content_ulid')->nullable()->constrained(table: 'content', column: 'ulid')->cascadeOnUpdate()->nullOnDelete();

            $table->foreignId('submitted_by')->nullable()->constrained(table: 'users', column: 'id')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained(table: 'users', column: 'id')->cascadeOnUpdate()->nullOnDelete();

            $table->string('status')->nullable();
            $table->text('notes')->nullable();

            $table->string('ip_address')->nullable();
            $table->string('hostname')->nullable();
            $table->string('isp')->nullable();
            $table->string('organization')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('email_confirmed_at')->nullable();
            $table->timestamps();

            $table->foreign(['language_code', 'country_code'])->references(['code', 'country_code'])->on('languages')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
};
