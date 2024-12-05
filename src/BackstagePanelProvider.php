<?php

namespace Vormkracht10\Backstage;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Vormkracht10\Backstage\Http\Middleware\Filament\ContentNavigationItems;
use Vormkracht10\Backstage\Http\Middleware\Filament\ScopedBySite;
use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Pages\Dashboard;
use Vormkracht10\MediaPicker\MediaPickerPlugin;
use Vormkracht10\Backstage\Resources\TagResource;
use Vormkracht10\MediaPicker\Pages\Media\Library;
use Vormkracht10\Backstage\Resources\BlockResource;
use Vormkracht10\Backstage\Resources\ContentResource;
use Vormkracht10\Backstage\Resources\DomainResource;
use Vormkracht10\Backstage\Resources\FieldResource;
use Vormkracht10\Backstage\Resources\FormResource;
use Vormkracht10\Backstage\Resources\LanguageResource;
use Vormkracht10\Backstage\Resources\MediaResource;
use Vormkracht10\Backstage\Resources\MenuResource;
use Vormkracht10\Backstage\Resources\SettingResource;
use Vormkracht10\Backstage\Resources\SiteResource;
use Vormkracht10\Backstage\Resources\SiteResource\RegisterSite;
use Vormkracht10\Backstage\Resources\TemplateResource;
use Vormkracht10\Backstage\Resources\TypeResource;
use Vormkracht10\Backstage\Resources\UserResource;
use Vormkracht10\Backstage\Widgets\ContentUpdatesWidget;
use Vormkracht10\Backstage\Widgets\FormSubmissionsWidget;
use Vormkracht10\FilamentRedirects\RedirectsPlugin;

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

        return $panel
            ->id('backstage')
            ->path('backstage')
            ->default(config('backstage.panel.default'))
            ->tenant(Site::class)
            ->tenantRegistration(RegisterSite::class)
            ->databaseNotifications()
            ->spa()
            ->login()
            ->passwordReset()
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::hex(Schema::hasColumn('sites', 'default') ? (Site::default()?->primary_color ?? '#ff9900') : '#ff9900'),
            ])
            ->plugins([
                RedirectsPlugin::make(),
                MediaPickerPlugin::make()
                    ->configureTenant('site', Site::class),
            ])
            ->resources([
                BlockResource::class,
                ContentResource::class,
                DomainResource::class,
                FieldResource::class,
                FormResource::class,
                LanguageResource::class,
                MenuResource::class,
                SettingResource::class,
                SiteResource::class,
                TagResource::class,
                TemplateResource::class,
                TypeResource::class,
                UserResource::class,
            ])
            ->pages([
                Dashboard::class,
                Library::class
            ])
            ->widgets([
                ContentUpdatesWidget::class,
                FormSubmissionsWidget::class,
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
            ->viteTheme(config('backstage.panel.theme.path'))
            ->authMiddleware([
                Authenticate::class,
            ])->tenantMiddleware([
                ScopedBySite::class,
                ContentNavigationItems::class,
            ], isPersistent: true);
    }
}