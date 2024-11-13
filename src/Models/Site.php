<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Site extends Model
{
    use HasFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    public $incrementing = false;

    protected $keyType = 'string';

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    protected static function booted(): void
    {
        // static::addGlobalScope('site', function (Builder $query) {
        //     if (auth()->hasUser()) {
        //         // $query->where('site_id', auth()->user()->current_site_id);
        //     }
        // });
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }
}
