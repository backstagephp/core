<?php

namespace Backstage\Components\Counter;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CounterServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('counter')
            ->hasViews();
    }

    public function boot()
    {
        $this->publishes([
            $this->package->basePath('/../resources/components/counter') => resource_path("views/components/{$this->package->shortName()}"),
            $this->package->basePath('/../src/Components') => app_path('View/Components'),
        ], [
            "backstage-components-{$this->package->shortName()}",
        ]);

        View::addLocation($this->package->basePath('/../resources/'));

        if (! class_exists(\App\View\Components\Counter::class)) {
            Blade::component('counter', Components\Counter::class);
        }
    }
}
