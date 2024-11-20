<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Reedware\LaravelCompositeRelations\HasCompositeRelations;
use Vormkracht10\Backstage\Factories\LanguageFactory;

class Language extends Model
{
    use HasCompositeRelations;
    use HasFactory;

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
}
