<?php

namespace Vormkracht10\Backstage\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use Vormkracht10\Backstage\BackstageServiceProvider;
use Orchestra\Testbench\Attributes\WithMigration; 

#[WithMigration] 
class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            BackstageServiceProvider::class,
        ];
    }

    public function defineEnvironment($app)
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        foreach (glob(__DIR__.'/../config/*.php') as $filename)
        {
            $app['config']->set(pathinfo($filename)['filename'], require $filename);
        }
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');


        // foreach (glob(__DIR__.'/../database/migrations/*.php') as $filename)
        // {
        //     $migration = include $filename;
        //     $migration->up();
        // }
        /*
        $migration = include __DIR__.'/../database/migrations/create_backstage_table.php.stub';
        $migration->up();
        */
    }
}
