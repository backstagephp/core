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
        Schema::table('content', function (Blueprint $table) {
            $table->string('source_content_ulid')->nullable()->after('ulid');

            $table->foreign('source_content_ulid')
                ->references('ulid')
                ->on('content')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content', function (Blueprint $table) {
            $table->dropForeign(['source_content_ulid']);
            $table->dropColumn('source_content_ulid');
        });
    }
};
