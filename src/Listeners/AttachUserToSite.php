<?php

namespace Backstage\Listeners;

use Backstage\Laravel\Users\Events\Auth\UserCreated;
use Backstage\Models\Site;
use Backstage\Models\User;
use Filament\Facades\Filament;

class AttachUserToSite
{
    public function handle(UserCreated $event)
    {
        /** @var Site $site */
        $site = Filament::getTenant();

        /** @var User $user */
        $user = $event->user;

        if (! $site) {
            return;
        }

        $user->sites()->attach($site);
    }
}
