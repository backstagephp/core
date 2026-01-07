<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_relationships', function (Blueprint $table) {
            // Change model_id to string to support IDs, ULIDs, and UUIDs (max 36 chars)
            $table->string('model_id', 36)->change();
            $table->string('model_type')->change();
        });
    }

    public function down(): void
    {
        Schema::table('media_relationships', function (Blueprint $table) {
            // Revert to typical big integer if needed (unsafe if data exists)
            // $table->unsignedBigInteger('model_id')->change();
        });
    }
};
