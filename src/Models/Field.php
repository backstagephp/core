<?php

namespace Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;
use Backstage\Shared\HasPackageFactory;

class Field extends Model
{
    use HasPackageFactory;
    use HasRecursiveRelationships;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'config' => 'array',
        ];
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model', 'model_type', 'model_key', 'slug');
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Field::class, 'parent_ulid')->with('children')->orderBy('position');
    }
}
