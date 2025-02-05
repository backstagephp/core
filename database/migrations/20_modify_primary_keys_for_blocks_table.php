<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('blocks', function (Blueprint $table) {
            $table->dropPrimary();
            $table->ulid('ulid')->primary()->change();
        });

        Schema::table('block_site', function (Blueprint $table) {
            $table->ulid('block_ulid')->change();
            $table->foreign('block_ulid')->references('ulid')->on('blocks');
        });
    }
};
