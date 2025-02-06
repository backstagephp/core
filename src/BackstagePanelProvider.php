<?php

namespace Vormkracht10\Backstage;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Vormkracht10\Backstage\Http\Middleware\Filament\ScopedBySite;
use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Pages\Dashboard;
use Vormkracht10\Backstage\Resources\SiteResource\RegisterSite;

class BackstagePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_BEFORE,
            fn (): string => Blade::render(
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
            Css::make('filament-media-picker', base_path('vendor/vormkracht10/filament-media-picker/resources/dist/filament-media-picker.css')),
        ], package: 'vormkracht10/filament-media-picker');

        return $panel
            ->id('backstage')
            ->path('backstage')
            ->databaseNotifications()
            ->login()
            ->passwordReset()
            ->sidebarCollapsibleOnDesktop()
            ->unsavedChangesAlerts()
            ->default(config('backstage.panel.default'))
            ->plugins(config('backstage.panel.plugins'))
            ->resources(config('backstage.panel.resources'))
            ->widgets(config('backstage.panel.widgets'))
            ->pages([
                Dashboard::class,
            ])
            ->colors(fn () => [
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
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Content'),
                NavigationGroup::make()
                    ->label('Structure'),
                NavigationGroup::make()
                    ->label('Users'),
                NavigationGroup::make()
                    ->label('Setup'),
            ])
            ->tenant(Site::class)
            ->tenantRegistration(RegisterSite::class)
            ->tenantMiddleware([
                ScopedBySite::class,
            ], isPersistent: true)
            // enable spa mode for browsers except Safari
            ->spa(fn () => !( str_contains(strtolower(request()->userAgent()), 'safari') !== false && str_contains(strtolower(request()->userAgent()), 'chrome') === false));
    }
}
