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
        if (! Schema::hasColumn('menu_items', 'content_ulid')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->foreignUlid('content_ulid')->nullable()->after('parent_ulid')->constrained(table: 'content', column: 'ulid')->cascadeOnUpdate()->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('menu_items', 'content_ulid')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->dropForeign(['content_ulid']);
                $table->dropColumn('content_ulid');
            });
        }
    }
};
