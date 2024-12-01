<?php

namespace Vormkracht10\Backstage\Models;

use Vormkracht10\Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Form extends Model
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
        return $this->morphMany(Field::class, 'model');
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }
}
