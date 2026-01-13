<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('users.eloquent.user_login.table', 'user_logins'), function (Blueprint $table) {
            $table->text('url')->change();
        });
    }

    public function down(): void
    {
        Schema::table(config('users.eloquent.user_login.table', 'user_logins'), function (Blueprint $table) {
            $table->string('url', 255)->change();
        });
    }
};
