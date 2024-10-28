<?php

namespace Vormkracht10\Backstage;

use Filament\Panel;
use Filament\PanelProvider;
use Vormkracht10\Backstage\Models\Site;
use Filament\Http\Middleware\Authenticate;
use Vormkracht10\Backstage\Pages\Dashboard;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Vormkracht10\Backstage\Resources\TagResource;
use Vormkracht10\Backstage\Resources\SiteResource;
use Vormkracht10\Backstage\Resources\TypeResource;
use Vormkracht10\Backstage\Resources\UserResource;
use Vormkracht10\Backstage\Resources\FieldResource;
use Vormkracht10\Backstage\Resources\MediaResource;
use Vormkracht10\Backstage\Resources\DomainResource;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Vormkracht10\Backstage\Resources\ContentResource;
use Vormkracht10\Backstage\Resources\SettingResource;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Vormkracht10\Backstage\Resources\LanguageResource;
use Vormkracht10\Backstage\Resources\RedirectResource;
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
            // ->tenant(Site::class)
            ->spa()
            ->login()
            ->passwordReset()
            ->unsavedChangesAlerts(fn() => app()->isProduction())
            ->sidebarCollapsibleOnDesktop()
            ->plugins([
                // BackstagePlugin::make(),
            ])
            ->resources([
                ContentResource::class,
                DomainResource::class,
                FieldResource::class,
                LanguageResource::class,
                MediaResource::class,
                RedirectResource::class,
                SettingResource::class,
                SiteResource::class,
                TagResource::class,
                TypeResource::class,
                UserResource::class,
            ])
            ->pages([
                Dashboard::class,
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
