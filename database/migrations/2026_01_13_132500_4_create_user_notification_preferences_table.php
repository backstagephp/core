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
        Schema::create(config('users.eloquent.user_notification_preferences.table', 'user_notification_preferences'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            $table->string('navigation_type');
            $table->timestamps();

            $table->unique(['user_id', 'navigation_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('users.eloquent.user_notification_preferences.table', 'user_notification_preferences'));
    }
};
