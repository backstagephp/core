<?php

namespace Backstage\Models;

use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Backstage\Fields\Concerns\HasFields;

class Type extends Model
{
    use HasFields;
    use HasPackageFactory;

    protected $primaryKey = 'slug';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }
}
