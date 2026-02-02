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

        MenuItem::where('url', $event->oldUrl)
            ->update(['url' => $event->newUrl]);
    }
}
