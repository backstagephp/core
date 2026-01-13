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
        Schema::table(config('users.eloquent.user.table', 'users'), function (Blueprint $table) {
            $table->enum('width_preference', ['full', '7xl'])
                ->default('7xl')
                ->after('remember_token')
                ->comment('The user\'s preference for the content width. The default is 7xl.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('users.eloquent.user.table', 'users'), function (Blueprint $table) {
            $table->dropColumn('width_preference');
        });
    }
};
