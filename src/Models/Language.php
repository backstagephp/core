<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Model;
use Vormkracht10\Backstage\Scopes\ScopedBySite;
use Vormkracht10\Backstage\Factories\LanguageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Reedware\LaravelCompositeRelations\HasCompositeRelations;

class Language extends Model
{
    use HasCompositeRelations;
    use HasFactory;
    use ScopedBySite;

    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    protected static function newFactory()
    {
        return LanguageFactory::new();
    }

    public function site(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'language_site', 'code');
    }
}
