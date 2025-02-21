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
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignUlid('parent_ulid')->nullable()->change();
            $table->string('title')->nullable()->change();
            $table->foreignUlid('content_ulid')->nullable()->after('parent_ulid');
            $table->dropForeign(['parent_ulid']);
            $table->dropColumn('slug');
            $table->boolean('include_children')->default(false)->after('content_ulid');
        });
    }
};
