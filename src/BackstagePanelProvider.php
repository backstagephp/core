<?php

namespace Vormkracht10\Backstage;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Pages\Dashboard;
use Vormkracht10\Backstage\Resources\ContentResource;
use Vormkracht10\Backstage\Resources\DomainResource;
use Vormkracht10\Backstage\Resources\FieldResource;
use Vormkracht10\Backstage\Resources\FormResource;
use Vormkracht10\Backstage\Resources\LanguageResource;
use Vormkracht10\Backstage\Resources\MediaResource;
use Vormkracht10\Backstage\Resources\MenuResource;
use Vormkracht10\Backstage\Resources\RedirectResource;
use Vormkracht10\Backstage\Resources\SettingResource;
use Vormkracht10\Backstage\Resources\SiteResource;
use Vormkracht10\Backstage\Resources\TagResource;
use Vormkracht10\Backstage\Resources\TypeResource;
use Vormkracht10\Backstage\Resources\UserResource;

class BackstagePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('backstage')
            ->path('backstage')
            ->default(config('backstage.default_panel'))
            // ->tenant(Site::class)
            ->databaseNotifications()
            ->spa()
            ->login()
            ->passwordReset()
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->plugins([
                // BackstagePlugin::make(),
            ])
            ->resources([
                ContentResource::class,
                DomainResource::class,
                FieldResource::class,
                FormResource::class,
                LanguageResource::class,
                MediaResource::class,
                MenuResource::class,
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
