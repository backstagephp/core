<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Model;
use Vormkracht10\Backstage\Factories\TypeFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Type extends Model
{
    use HasFactory;

    protected $primaryKey = 'slug';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    protected static function newFactory()
    {
        return TypeFactory::new();
    }

    public function fields(): MorphMany
    {
        return $this->morphMany(Field::class, 'slug', 'model_type', 'model_key');
    }
}
