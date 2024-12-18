<?php

namespace Vormkracht10\Backstage;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Vormkracht10\Backstage\Models\Site;
use Filament\Http\Middleware\Authenticate;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Facades\FilamentAsset;
use Vormkracht10\Backstage\Pages\Dashboard;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Vormkracht10\Backstage\Resources\SiteResource\RegisterSite;
use Vormkracht10\Backstage\Http\Middleware\Filament\ScopedBySite;
use Vormkracht10\Backstage\Http\Middleware\Filament\ContentNavigationItems;

class BackstagePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_BEFORE,
            fn(): string => Blade::render(
                <<<'HTML'
                <script>
                document.addEventListener('livewire:navigated', () => {
                    if(window.matchMedia('(prefers-color-scheme: dark)').matches || localStorage.getItem('theme') === 'dark') {
                        document.getElementsByTagName('html')[0].style.backgroundColor = '#030712';

                        var meta = document.createElement('meta');
                        meta.setAttribute('name', 'background-color');
                        meta.setAttribute('content', '#030712');
                        document.getElementsByTagName('head')[0].appendChild(meta);

                        var meta = document.createElement('meta');
                        meta.setAttribute('name', 'theme-color');
                        meta.setAttribute('content', '#030712');
                        document.getElementsByTagName('head')[0].appendChild(meta);
                    }
                    else {
                        document.getElementsByTagName('html')[0].style.backgroundColor = '#ffffff';

                        var meta = document.createElement('meta');
                        meta.setAttribute('name', 'background-color');
                        meta.setAttribute('content', '#ffffff');
                        document.getElementsByTagName('head')[0].appendChild(meta);

                        var meta = document.createElement('meta');
                        meta.setAttribute('name', 'theme-color');
                        meta.setAttribute('content', '#ffffff');
                        document.getElementsByTagName('head')[0].appendChild(meta);
                    }
                });
                </script>
                HTML
            ),
        );

        FilamentAsset::register([
            Css::make('filament-media-picker', __DIR__ . '/../vendor/vormkracht10/filament-media-picker/resources/dist/filament-media-picker.css'),
        ], package: 'vormkracht10/filament-media-picker');

        return $panel
            ->id('backstage')
            ->path('backstage')
            ->databaseNotifications()
            ->login()
            ->passwordReset()
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->unsavedChangesAlerts()
            ->default(config('backstage.panel.default'))
            ->plugins(config('backstage.panel.pluginsou'))
            ->resources(config('backstage.panel.resources'))
            ->widgets(config('backstage.panel.widgets'))
            ->pages([
                Dashboard::class,
            ])
            ->colors(fn() => [
                'primary' => Color::hex(Site::default()?->primary_color ?: '#ff9900'),
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
            ])
            ->tenant(Site::class)
            ->tenantRegistration(RegisterSite::class)
            ->tenantMiddleware([
                ScopedBySite::class,
                ContentNavigationItems::class,
            ], isPersistent: true);
    }
}