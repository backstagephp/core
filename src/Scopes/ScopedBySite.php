<?php

namespace Vormkracht10\Backstage\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Vormkracht10\Backstage\Models\Site;

trait ScopedBySite
{
    public static function bootScopedBySite(): void
    {
        static::addGlobalScope('site', function (Builder $query) {
            if (auth()->hasUser()) {
                $query->whereHas('site', function (Builder $query) {
                    $query->where('ulid', auth()->current_site_id ?? Site::default()->ulid);
                });
            }
        });
    }
}
