<?php

namespace Backstage\Listeners;

use Backstage\Models\MenuItem;
use Backstage\Redirects\Laravel\Events\UrlHasChanged;

class UpdateMenuItemUrls
{
    public function handle(UrlHasChanged $event): void
    {
        if (! $event->oldUrl || ! $event->newUrl) {
            return;
        }

        // Convert absolute URLs to relative paths for menu items
        $oldRelativePath = $this->convertToRelativePath($event->oldUrl);
        $newRelativePath = $this->convertToRelativePath($event->newUrl);

        // Update menu items that have the old relative path
        MenuItem::where('url', $oldRelativePath)
            ->update(['url' => $newRelativePath]);

        // Also update menu items that still have the old absolute URL
        // (for backwards compatibility with existing data)
        MenuItem::where('url', $event->oldUrl)
            ->update(['url' => $newRelativePath]);
    }

    /**
     * Convert an absolute URL to a relative path by removing the protocol and domain.
     */
    private function convertToRelativePath(string $url): string
    {
        // Parse the URL to extract the path
        $parsed = parse_url($url);

        // Get the path component (defaults to '/' if not present)
        $path = $parsed['path'] ?? '/';

        return $path;
    }
}
