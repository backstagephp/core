<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Block extends Model
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

    public function fields(): MorphMany
    {
        return $this->morphMany(Field::class, 'model', 'model_type', 'model_key', 'slug');
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }
}
