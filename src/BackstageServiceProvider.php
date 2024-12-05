<?php

namespace Vormkracht10\Backstage;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Vormkracht10\Backstage\Commands\BackstageSeedCommand;
use Vormkracht10\Backstage\Models\Block;
use Vormkracht10\Backstage\Models\Menu;
use Vormkracht10\Backstage\Models\Type;
use Vormkracht10\Backstage\Observers\MenuObserver;
use Vormkracht10\Backstage\Testing\TestsBackstage;
use Vormkracht10\Backstage\View\Components\Blocks;
use Vormkracht10\Backstage\View\Components\Page;

class BackstageServiceProvider extends PackageServiceProvider
{
    public static string $name = 'backstage';

    public static string $viewNamespace = 'backstage';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('vormkracht10/backstage');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/backstage/{$file->getFilename()}"),
                ], 'backstage-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsBackstage);

        Relation::enforceMorphMap([
            'block' => 'Vormkracht10\Backstage\Models\Block',
            'content' => 'Vormkracht10\Backstage\Models\Content',
            'domain' => 'Vormkracht10\Backstage\Models\Domain',
            'field' => 'Vormkracht10\Backstage\Models\Field',
            'form' => 'Vormkracht10\Backstage\Models\Form',
            'language' => 'Vormkracht10\Backstage\Models\Language',
            'menu' => 'Vormkracht10\Backstage\Models\Menu',
            'setting' => 'Vormkracht10\Backstage\Models\Setting',
            'site' => 'Vormkracht10\Backstage\Models\Site',
            'tag' => 'Vormkracht10\Backstage\Models\Tag',
            'type' => 'Vormkracht10\Backstage\Models\Type',
            'user' => 'Vormkracht10\Backstage\Models\User',
        ]);

        Route::bind('type', function (string $slug) {
            return Type::where('slug', $slug)->firstOrFail();
        });

        Route::bind('block', function (string $slug) {
            return Block::where('slug', $slug)->firstOrFail();
        });

        Select::configureUsing(function (Select $select): void {
            $select->native(false);
            // $select->searchable();
        });

        Menu::observe(MenuObserver::class);

        Filament::registerNavigationGroups([
            NavigationGroup::make()
                ->label('Content'),
            NavigationGroup::make()
                ->label('Structure'),
            NavigationGroup::make()
                ->label('Users'),
            NavigationGroup::make()
                ->label('Setup'),
        ]);

        $this->app->register(Providers\RequestServiceProvider::class);
        $this->app->register(Providers\RouteServiceProvider::class);

        collect($this->app['config']['backstage']['components']['blocks'] ?? [])
            ->each(fn($component) => Blade::component(Str::slug(last(explode('\\', $component))), $component));

        Blade::component('blocks', Blocks::class);
        Blade::component('page', Page::class);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'vormkracht10/backstage';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('backstage', __DIR__ . '/../resources/dist/components/backstage.js'),
            // Css::make('backstage-styles', __DIR__ . '/../resources/dist/backstage.css'),
            // Js::make('backstage-scripts', __DIR__ . '/../resources/dist/backstage.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            BackstageSeedCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_languages_table',
            'create_sites_table',
            'create_types_table',
            'create_fields_table',
            'create_settings_table',
            'create_content_table',
            'create_templates_table',
            'create_content_field_values_table',
            'create_blocks_table',
            'create_menus_table',
            'create_menu_items_table',
            'create_domains_table',
            'create_forms_table',
            'create_form_actions_table',
            'create_form_submissions_table',
            'create_form_submission_values_table',
            'create_tags_tables',

            'create_notifications_table',
            'add_columns_to_users_table',
        ];
    }
}
