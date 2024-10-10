<?php

namespace Vormkracht10\Backstage;

use Vormkracht10\Backstage\Resources\ContentResource;
use Filament\Panel;
use Filament\PanelProvider;
use Vormkracht10\Backstage\Models\Site;
use Filament\Http\Middleware\Authenticate;
use Vormkracht10\Backstage\BackstagePlugin;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

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
            ->unsavedChangesAlerts(fn() => app()->isProduction())
            ->plugins([
                // BackstagePlugin::make(),
            ])
            ->resources([
                ContentResource::class,
            ])
            ->pages([
                // ...
            ])
            ->widgets([
                // ...
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
