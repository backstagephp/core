<?php

namespace Backstage\Models\Concerns;

use Filament\Facades\Filament;

trait BelongsToCurrentTenant
{
    public static function bootBelongsToCurrentTenant(): void
    {
        static::creating(function ($model) {
            if (! $model->site_ulid && Filament::getTenant()) {
                $model->site_ulid = Filament::getTenant()->ulid;
            }
        });
    }
}
