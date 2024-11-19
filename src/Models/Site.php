<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public static function default(): Site
    {
        return Site::firstWhere('default', 1);
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
