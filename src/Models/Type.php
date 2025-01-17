<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Model;
use Vormkracht10\Backstage\Models\Template;
use Vormkracht10\Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Type extends Model
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
        return $this->morphMany(Field::class, 'slug', 'model_type', 'model_key')
            ->orderBy('position');
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_slug', 'slug');
    }
}