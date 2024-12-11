<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vormkracht10\Backstage\Shared\HasPackageFactory;
use Vormkracht10\Backstage\Shared\HasTags;

class Site extends Model
{
    use HasPackageFactory;
    use HasTags;
    use HasUlids;

    protected $primaryKey = 'ulid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public static function default(): ?Site
    {
        return Site::firstWhere('default', 1);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(Type::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function blocks(): BelongsToMany
    {
        return $this->belongsToMany(Block::class);
    }

    public function forms(): BelongsToMany
    {
        return $this->belongsToMany(Form::class);
    }
}
