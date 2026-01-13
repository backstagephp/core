<?php

use Illuminate\Support\Facades\Schema;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(config('users.eloquent.user.table', 'users'), function (Blueprint $table) {
            $table->enum('sub_navigation_preference', ['start', 'end', 'top'])
                ->default('top')
                ->after('remember_token')
                ->comment('The user\'s preference for the sub navigation position. The default is top.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('users.eloquent.user.table', 'users'), function (Blueprint $table) {
            $table->dropColumn('sub_navigation_preference');
        });
    }
};
