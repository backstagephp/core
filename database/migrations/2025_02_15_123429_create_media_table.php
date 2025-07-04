<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable(app(\Backstage\Media\Models\Media::class)->getTable())) {
            return;
        }

        Schema::create(app(\Backstage\Media\Models\Media::class)->getTable(), function (Blueprint $table) {
            $table->ulid('ulid')->primary();

            $table->string('disk')->index();
            $table->foreignId('uploaded_by')->nullable()->index()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('filename');
            $table->string('original_filename')->nullable();
            $table->string('extension')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();

            // Nullable dimensions for non-image files
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            $table->string('checksum', 64)->index();
            $table->boolean('public')->default(true);

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(app(\Backstage\Media\Models\Media::class)->getTable());
    }
};
