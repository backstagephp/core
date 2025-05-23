<?php

namespace Backstage;

use SplFileInfo;
use Backstage\Models\Menu;
use Backstage\Models\Site;
use Backstage\Models\Type;
use Backstage\Models\User;
use Backstage\Models\Block;
use Backstage\Models\Media;
use Illuminate\Support\Str;
use Filament\Support\Assets\Asset;
use Backstage\CustomFields\Builder;
use Backstage\Events\FormSubmitted;
use Backstage\View\Components\Page;
use Backstage\Observers\MenuObserver;
use Backstage\Testing\TestsBackstage;
use Backstage\View\Components\Blocks;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Backstage\CustomFields\CheckboxList;
use Backstage\Resources\ContentResource;
use Backstage\Listeners\ExecuteFormActions;
use Filament\Support\Facades\FilamentAsset;
use Backstage\Commands\BackstageSeedCommand;
use Backstage\Commands\BackstageUpgrade;
use Backstage\Media\Resources\MediaResource;
use Filament\Support\Enums\VerticalAlignment;
use Livewire\Features\SupportTesting\Testable;
use Filament\Notifications\Livewire\Notifications;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;

class BackstageServiceProvider extends PackageServiceProvider
{
    public static string $name = 'backstage';

    public static string $viewNamespace = 'backstage';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile([
                'backstage/cms',
                'backstage/media',
            ])
            ->hasMigrations($this->getMigrations())
            ->hasTranslations()
            ->hasViews(static::$viewNamespace)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->startWith(function (InstallCommand $command) {
                        $command->info('Welcome to the Backstage setup process.');
                        $command->comment("Don't trip over the wires; this is where the magic happens.");
                        $command->comment('Let\'s get started!');

                        // if ($command->confirm('Would you like us to install Backstage for you?', true)) {
                        $command->comment('Lights, camera, action! Setting up for the show...');

                        $command->comment('Preparing stage...');

                        $command->callSilently('vendor:publish', [
                            '--tag' => 'translations-migrations',
                            '--force' => true,
                        ]);

                        $command->callSilently('vendor:publish', [
                            '--tag' => 'translations-config',
                            '--force' => true,
                        ]);

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

                        $this->runFilamentFieldsCommand($command);

                        $this->writeMediaPickerConfig();

                        $command->callSilently('vendor:publish', [
                            '--tag' => 'media-migrations',
                            '--force' => true,
                        ]);

                        $command->comment('Clean the decor...');

                        $command->callSilently('migrate:fresh', [
                            '--force' => true,
                        ]);

                        $command->comment('Hanging up lights...');
                        $command->callSilently('backstage:seed', [
                            '--force' => true,
                        ]);

                        $command->comment('Plugin wires...');
                        $command->callSilently('filament:assets');

                        $command->comment('Turn on the lights...');
                        $key = 'AUTH_MODEL';
                        $value = '\Backstage\Models\User';
                        $path = app()->environmentFilePath();
                        file_put_contents($path, file_get_contents($path) . PHP_EOL . $key . '=' . $value);

                        $command->comment('Raise the curtain...');
                        // }
                    })
                    ->endWith(function (InstallCommand $command) {
                        $command->info('The stage is cleared for a fresh start');
                        $command->comment('You can now go on stage and start creating!');
                    })
                    ->askToStarRepoOnGitHub('backstage/cms');
            });
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
            'block' => 'Backstage\Models\Block',
            'content' => 'Backstage\Models\Content',
            'domain' => 'Backstage\Models\Domain',
            'field' => 'Backstage\Fields\Models\Field',
            'form' => 'Backstage\Models\Form',
            'language' => 'Backstage\Models\Language',
            'translation' => 'Backstage\Translations\Laravel\Models\Language',
            'menu' => 'Backstage\Models\Menu',
            'setting' => 'Backstage\Models\Setting',
            'site' => 'Backstage\Models\Site',
            'tag' => 'Backstage\Models\Tag',
            'type' => 'Backstage\Models\Type',
            'user' => 'Backstage\Models\User',
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

        collect($this->app['config']['backstage']['cms']['components']['blocks'] ?? [])
            ->each(function ($component) {
                Blade::component(Str::slug(last(explode('\\', $component))), $component);
                Backstage::registerComponent($component);
            });

        Blade::component('blocks', Blocks::class);
        Blade::component('page', Page::class);


        Notifications::verticalAlignment(VerticalAlignment::End);
        Notifications::alignment(Alignment::End);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'backstage/cms';
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
            BackstageUpgrade::class,
        ];
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
        return collect(app(Filesystem::class)->files(__DIR__ . '/../database/migrations'))
            ->map(fn (SplFileInfo $file) => str_replace('.php', '', $file->getBasename()))
            ->sortBy(fn ($filename) => (int) explode('_', $filename)[0])
            ->values()
            ->toArray();
    }

    private function generateMediaPickerConfig(): array
    {
        $config = [
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
            'tenant_ownership_relationship_name' => 'site',
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

        config(['media-picker' => $config]);

        return $config;
    }

    private function runFilamentFieldsCommand(InstallCommand $command): void
    {
        $command->callSilently('vendor:publish', [
            '--tag' => 'fields-config',
            '--force' => true,
        ]);

        $this->writeFilamentFieldsConfig();

        $command->callSilently('vendor:publish', [
            '--tag' => 'fields-migrations',
            '--force' => true,
        ]);

        $migrationsPath = database_path('migrations');

        // Specifically look for the fields migration file
        $fieldsMigrationFiles = glob($migrationsPath . '/*_create_fields_table.php');

        // Get timestamp from create_sites_table migration
        $sitesMigrationFiles = glob($migrationsPath . '/*_create_sites_table.php');
        $date = substr(basename($sitesMigrationFiles[0]), 0, 17);

        if (! empty($fieldsMigrationFiles)) {
            $oldName = $fieldsMigrationFiles[0];
            $newName = $migrationsPath . '/' . $date . '_03_create_fields_table.php';
            rename($oldName, $newName);
        }
    }

    private function writeFilamentFieldsConfig(?string $path = null): void
    {
        $path ??= config_path('backstage/fields.php');

        // Ensure directory exists
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Generate the config file content
        $configContent = "<?php\n\n";
        $configContent .= "use Backstage\Models\Site;\n";
        $configContent .= "use Backstage\CustomFields\Builder;\n";
        $configContent .= "use Backstage\Resources\ContentResource;\n";

        // Custom export function to create more readable output
        $configContent .= 'return ' . $this->customVarExport($this->generateFilamentFieldsConfig()) . ";\n";

        file_put_contents($path, $configContent);
    }

    private function generateFilamentFieldsConfig(): array
    {
        $config = [

            'tenancy' => [
                'is_tenant_aware' => false,
                'relationship' => 'tenant',
                'key' => 'id',
                // 'model' => \App\Models\Tenant::class,
            ],

            'custom_fields' => [
                Builder::class,
                CheckboxList::class,
                Select::class,
            ],

            'selectable_resources' => [
                ContentResource::class,
            ],
        ];

        config(['fields' => $config]);

        return $config;
    }

    private function writeMediaPickerConfig(?string $path = null): void
    {
        $path ??= config_path('backstage/media.php');

        // Ensure directory exists
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Generate the config file content
        $configContent = "<?php\n\n";
        $configContent .= "use Backstage\Models\Site;\n";
        $configContent .= "use Backstage\Models\User;\n";
        $configContent .= "use Backstage\Models\Media;\n\n";
        $configContent .= "use Backstage\Media\Resources\MediaResource;\n\n";

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
