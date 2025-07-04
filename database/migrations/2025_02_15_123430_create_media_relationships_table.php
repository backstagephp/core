<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('media_relationships')) {
            return;
        }

        Schema::create('media_relationships', function (Blueprint $table) {
            $table->id();

            // Media ULID reference
            $table->foreignUlid('media_ulid')
                ->references('ulid')
                ->on(app(config('backstage.media.model', \Backstage\Media\Models\Media::class))->getTable())
                ->cascadeOnDelete();

            // Polymorphic model relationship
            $table->morphs('model');

            // Optional position for each relationship
            $table->unsignedInteger('position')->nullable();

            // Additional metadata for the relationship
            $table->json('meta')->nullable();

            $table->timestamps();

            // Unique constraint to prevent duplicate relationships
            $table->unique(['media_ulid', 'model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_relationships');
    }
};
