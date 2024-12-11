<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Reedware\LaravelCompositeRelations\HasCompositeRelations;
use Vormkracht10\Backstage\Shared\HasPackageFactory;

class Language extends Model
{
    use HasCompositeRelations;
    use HasPackageFactory;

    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public static function default(): ?Language
    {
        return static::firstWhere('default', 1);
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'language_site', 'code');
    }
}
