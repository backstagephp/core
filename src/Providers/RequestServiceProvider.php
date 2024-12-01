<?php

namespace Vormkracht10\Backstage\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Vormkracht10\Backstage\Models\Domain;
use Vormkracht10\Backstage\Models\Content;

class RequestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Request::macro('content', function () {
            return once(fn() => Content::where('path', $this->path())->first());
        });

        Request::macro('domain', function () {
            return once(fn() => Domain::whereRaw("REPLACE('www.', '', name)", str_replace('www.', '', $this->getHost()))->first());
        });

        Request::macro('language', function () {
            return once(fn() => $this->content()->language);
        });

        Request::macro('site', function () {
            return once(fn() => $this->domain()->site);
        });
    }
}
