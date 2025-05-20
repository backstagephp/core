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
        Schema::create('content_relation', function (Blueprint $table) {
            $table->id();
            
            $table->string('source_type');
            $table->ulid('source_ulid');
            
            $table->string('target_type');
            $table->ulid('target_ulid');
                        
            $table->index(['source_type', 'source_ulid']);
            $table->index(['target_type', 'target_ulid']);
        });
    }
};
