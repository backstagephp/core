<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Vormkracht10\Backstage\Scopes\ScopedBySite;

class Menu extends Model
{
    use HasFactory;
    use ScopedBySite;

    protected $primaryKey = 'slug';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function site(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }
}
