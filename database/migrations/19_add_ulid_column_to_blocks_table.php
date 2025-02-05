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
            $table->ulid('ulid')->nullable()->after('slug');
        });

        DB::table('blocks')->whereNull('ulid')
            ->orderBy('slug')
            ->each(function ($block) {
                DB::table('blocks')
                    ->where('slug', $block->slug)
                    ->update(['ulid' => Str::ulid()]);
            });

        Schema::table('block_site', function (Blueprint $table) {
            $table->ulid('block_ulid')->nullable();
        });

        DB::table('block_site')
            ->join('blocks', 'blocks.slug', '=', 'block_site.block_slug')
            ->update(['block_site.block_ulid' => DB::raw('blocks.ulid')]);

        Schema::table('block_site', function (Blueprint $table) {
            $table->dropForeign(['block_slug']);
            $table->dropColumn('block_slug');
        });

        // All 'fields' where 'model_type' = 'block' should change model_key to 'ulid'

        $fields = DB::table('fields')
            ->where('model_type', 'block')
            ->get();

        foreach ($fields as $field) {
            $block = DB::table('blocks')
                ->where('slug', $field->model_key)
                ->first();

            DB::table('fields')
                ->where('id', $field->id)
                ->update(['model_key' => $block->ulid]);
        }
    }
};
