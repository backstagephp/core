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
        Schema::create(config('users.eloquent.user_traffic.table', 'user_traffic'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('method', 10);
            $table->string('path');
            $table->text('full_url');
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->string('route_name')->nullable();
            $table->string('route_action')->nullable();
            $table->json('route_parameters')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('users.eloquent.user_traffic.table', 'user_traffic'));
    }
};
