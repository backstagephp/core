<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Try to drop foreign key (might fail if already dropped)
        try {
            Schema::table('media_relationships', function (Blueprint $table) {
                $table->dropForeign(['media_ulid']);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Ignore if foreign key does not exist
        }

        // 2. Try to drop unique index (might fail if already dropped)
        try {
            Schema::table('media_relationships', function (Blueprint $table) {
                $table->dropUnique(['media_ulid', 'model_type', 'model_id']);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Ignore if index does not exist
        }

        // 3. Try to add new index (might fail if already exists)
        try {
            Schema::table('media_relationships', function (Blueprint $table) {
                $table->index(['model_type', 'model_id', 'position']);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Ignore if index already exists
        }

        // 4. Re-add foreign key (using safe 'foreign' method)
        Schema::table('media_relationships', function (Blueprint $table) {
            // We use a separate call here to ensure we don't catch unexpected errors in the definition
            // But we might want to check if it exists?
            // Generically adding a FK usually fails if it exists with same name.
            // Since we know we tried to drop it in step 1, this should be safe unless step 1 failed unrelatedly.
            // However, to be extra safe against "Constraint already exists":
            try {
                $table->foreign('media_ulid')
                    ->references('ulid')
                    ->on(app(config('backstage.media.model', \Backstage\Media\Models\Media::class))->getTable())
                    ->cascadeOnDelete();
            } catch (\Illuminate\Database\QueryException $e) {
                // assume it exists if it fails
            }
        });
    }

    public function down(): void
    {
        Schema::table('media_relationships', function (Blueprint $table) {
            $table->dropIndex(['model_type', 'model_id', 'position']);
            $table->unique(['media_ulid', 'model_type', 'model_id']);
        });
    }
};
