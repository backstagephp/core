<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Model;
use Vormkracht10\Backstage\Scopes\ScopedBySite;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Template extends Model
{
    use HasFactory;
    use HasUlids;
    use ScopedBySite;

    protected $primaryKey = 'slug';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function fields(): MorphToMany
    {
        return $this->morphToMany(Field::class, 'fieldable');
    }

    public function site(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }
}
