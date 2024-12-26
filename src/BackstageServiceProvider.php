<?php

namespace Vormkracht10\Backstage;

use Illuminate\Support\Str;
use Filament\Support\Assets\Asset;
use Filament\Forms\Components\Select;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Vormkracht10\Backstage\Models\Menu;
use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Models\Type;
use Vormkracht10\Backstage\Models\User;
use Vormkracht10\Backstage\Models\Block;
use Vormkracht10\Backstage\Models\Media;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Facades\FilamentAsset;
use Livewire\Features\SupportTesting\Testable;
use Vormkracht10\Backstage\Events\FormSubmitted;
use Vormkracht10\Backstage\View\Components\Page;
use Vormkracht10\Backstage\Observers\MenuObserver;
use Vormkracht10\Backstage\Testing\TestsBackstage;
use Vormkracht10\Backstage\View\Components\Blocks;
use Illuminate\Database\Eloquent\Relations\Relation;
use Vormkracht10\Backstage\Contracts\FieldInspector;
use Vormkracht10\MediaPicker\Resources\MediaResource;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Vormkracht10\Backstage\Listeners\ExecuteFormActions;
use Vormkracht10\Backstage\Commands\BackstageSeedCommand;
use Vormkracht10\Backstage\Services\FieldInspectionService;
use Vormkracht10\Backstage\Providers\RequestServiceProvider;

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
                    ->startWith(function (InstallCommand $command) {
                        $command->info('Welcome to the Backstage setup process.');
                        $command->comment("Don't trip over the wires; this is where the magic happens.");
                        $command->comment('Let\'s get started!');

                        if ($command->confirm('Would you like us to install Backstage for you?', true)) {
                            $command->comment('Lights, camera, action! Setting up for the show...');

                            $command->callSilently('vendor:publish', [
                                '--tag' => 'backstage-migrations',
                                '--force' => true,
                            ]);

                            $command->callSilently('vendor:publish', [
                                '--tag' => 'backstage-config',
                                '--force' => true,
                            ]);

                            $command->callSilently('vendor:publish', [
                                '--tag' => 'redirects-migrations',
                                '--force' => true,
                            ]);

                            $this->writeMediaPickerConfig();

                            $command->callSilently('vendor:publish', [
                                '--tag' => 'media-picker-migrations',
                                '--force' => true,
                            ]);

                            $command->callSilently('migrate:fresh', [
                                '--force' => true,
                            ]);

                            $command->callSilently('backstage:seed', [
                                '--force' => true,
                            ]);
                        }
                    })
                    ->endWith(function (InstallCommand $command) {
                        $command->info('The stage is cleared for a fresh start');
                        $command->comment('You can now go on stage and start creating!');
                    })
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

        Event::listen(FormSubmitted::class, ExecuteFormActions::class);

        $this->app->register(Providers\RequestServiceProvider::class);
        $this->app->register(Providers\RouteServiceProvider::class);

        $this->app->bind(FieldInspector::class, FieldInspectionService::class);

        collect($this->app['config']['backstage']['components']['blocks'] ?? [])
            ->each(function ($component) {
                Blade::component(Str::slug(last(explode('\\', $component))), $component);
                Backstage::registerComponent($component);
            });

        collect($this->app['config']['backstage']['fields'] ?? [])
            ->each(function ($field) {
                Backstage::registerField($field);
            });

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

    private function generateMediaPickerConfig(): array
    {
        return [
            'accepted_file_types' => [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/svg+xml',
                'video/mp4',
                'video/webm',
                'audio/mpeg',
                'audio/ogg',
                'application/pdf',
            ],
            'directory' => 'media',
            'disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),
            'should_preserve_filenames' => false,
            'should_register_navigation' => true,
            'visibility' => 'public',
            'is_tenant_aware' => true,
            'tenant_ownership_relationship_name' => 'tenant',
            'tenant_relationship' => 'site',
            'tenant_model' => Site::class,
            'model' => Media::class,
            'user_model' => User::class,
            'resources' => [
                'label' => 'Media',
                'plural_label' => 'Media',
                'navigation_group' => null,
                'navigation_label' => 'Media',
                'navigation_icon' => 'heroicon-o-photo',
                'navigation_sort' => null,
                'navigation_count_badge' => false,
                'resource' => MediaResource::class,
            ],
        ];
    }

    private function writeMediaPickerConfig(?string $path = null): void
    {
        $path ??= config_path('media-picker.php');

        // Ensure directory exists
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Generate the config file content
        $configContent = "<?php\n\n";
        $configContent .= "use Vormkracht10\Backstage\Models\Site;\n";
        $configContent .= "use Vormkracht10\Backstage\Models\User;\n";
        $configContent .= "use Vormkracht10\MediaPicker\Models\Media;\n\n";
        $configContent .= "use Vormkracht10\MediaPicker\Resources\MediaResource;\n\n";

        // Custom export function to create more readable output
        $configContent .= 'return ' . $this->customVarExport($this->generateMediaPickerConfig()) . ";\n";

        file_put_contents($path, $configContent);
    }

    private function customVarExport($var, $indent = ''): string
    {
        switch (gettype($var)) {
            case 'string':
                // Specifically handle class references
                if (str_contains($var, '\\')) {
                    // Extract the short name and return as Class::class
                    $parts = explode('\\', $var);

                    return end($parts) . '::class';
                }

                // For regular strings, keep existing behavior
                return "'" . addslashes($var) . "'";
            case 'array':
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $result = "[\n";
                foreach ($var as $key => $value) {
                    $result .= $indent . '    ';
                    if (! $indexed) {
                        $result .= "'" . $key . "' => ";
                    }
                    $result .= $this->customVarExport($value, $indent . '    ') . ",\n";
                }
                $result .= $indent . ']';

                return $result;
            case 'boolean':
                return $var ? 'true' : 'false';
            case 'NULL':
                return 'null';
            default:
                return var_export($var, true);
        }
    }
}
