<?php

namespace Vormkracht10\Backstage;

use Filament\Http\Middleware\Authenticate;
use Filament\Panel;
use Filament\PanelProvider;
use Vormkracht10\Backstage\Models\Site;

class BackstagePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('backstage')
            ->path('backstage')
            ->default(config('backstage.default_panel'))
            ->tenant(Site::class)
            ->spa()
            ->login()
            ->passwordReset()
            ->unsavedChangesAlerts(fn () => app()->isProduction())
            ->resources([
                // ...
            ])
            ->pages([
                // ...
            ])
            ->widgets([
                // ...
            ])
            ->middleware([
                // ...
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
