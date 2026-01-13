<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('users.eloquent.user.table', 'users'), function (Blueprint $table) {
            if(!Schema::hasColumn($table->getTable(), 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table(config('users.eloquent.user.table', 'users'), function (Blueprint $table) {
            if(Schema::hasColumn($table->getTable(), 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
