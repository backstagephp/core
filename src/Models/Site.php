<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;

class Site
{
    protected static function booted(): void
    {
        static::addGlobalScope('site', function (Builder $query) {
            if (auth()->hasUser()) {
                // $query->where('site_id', auth()->user()->current_site_id);
            }
        });
    }
}
