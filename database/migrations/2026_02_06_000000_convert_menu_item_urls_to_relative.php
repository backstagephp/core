<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert all menu item URLs from absolute to relative paths
        $menuItems = DB::table('menu_items')
            ->whereNotNull('url')
            ->where('url', '!=', '')
            ->get();

        foreach ($menuItems as $menuItem) {
            $url = $menuItem->url;

            // Only convert if it's an absolute URL (starts with http:// or https://)
            if (preg_match('#^https?://#i', $url)) {
                $parsed = parse_url($url);
                $relativePath = $parsed['path'] ?? '/';

                DB::table('menu_items')
                    ->where('ulid', $menuItem->ulid)
                    ->update(['url' => $relativePath]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We cannot reliably reverse this migration as we don't know the original domain
        // Users would need to manually update their menu items if they rollback
    }
};
