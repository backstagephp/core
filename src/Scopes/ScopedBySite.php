<?php

namespace Vormkracht10\Backstage\Scopes;

use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Models\Type;
use Illuminate\Database\Eloquent\Builder;
use Vormkracht10\Backstage\Models\Content;

trait ScopedBySite
{
    public static function bootScopedBySite(): void
    {
        static::addGlobalScope('site', function (Builder $query) {
            if (auth()->hasUser()) {
                $query->whereHas('site', function (Builder $query) {
                    $query->where('ulid', auth()->current_site_ulid ?? Site::default()->ulid);
                });
            }
        });
    }
}
