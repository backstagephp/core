<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Models\Field;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meta', function (Blueprint $table) {
            $table->ulid()->primary();
            $table->ulid('content_ulid');
            $table->ulid('field_ulid');
            $table->longText('value');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('content_ulid')->references('ulid')->on('content')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('field_ulid')->references('ulid')->on('fields')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
};
