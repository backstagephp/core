<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vormkracht10\Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Template extends Model
{
    use HasPackageFactory;

    protected $primaryKey = 'slug';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function fields(): MorphMany
    {
        return $this->morphMany(Field::class, 'model', 'model_type', 'model_key', 'slug')
            ->orderBy('position');
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }

    public function blocks(): BelongsToMany
    {
        return $this->belongsToMany(Block::class, 'block_template', 'template_slug', 'block_slug')
            ->withPivot('position')
            ->orderBy('position');
    }
}