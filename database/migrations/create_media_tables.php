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
        Schema::create('media', function (Blueprint $table) {
            $table->ulid()->primary();

            $table->foreignUlid('site_ulid')->constrained(table: 'sites', column: 'ulid')->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('language_code', 2);
            $table->char('country_code', 2);

            $table->string('disk')->index();
            $table->unsignedBigInteger('uploaded_by')->nullable()->index();
            $table->ulidMorphs('model');
            $table->string('filename');
            $table->string('extension');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->unsignedBigInteger('width');
            $table->unsignedBigInteger('height');
            $table->string('checksum', 32);
            $table->boolean('public')->default(true);
            $table->unsignedBigInteger('position');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign(['language_code', 'country_code'])->references(['code', 'country_code'])->on('languages')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users')->cascadeOnUpdate()->nullOnDelete();
        });
    }
};
