<?php

namespace Backstage\Models;

use Backstage\Fields\Concerns\HasFields;
use Backstage\Fields\Models\Schema;
use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        return [
            'parent_filters' => 'array',
        ];
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }

    public function getTemplateAttribute(): string
    {
        return 'types.' . $this->slug;
    }

    public function getTemplatePathAttribute(): string
    {
        return resource_path('views/types/' . $this->slug . '.blade.php');
    }

    public function schemas(): MorphMany
    {
        return $this->morphMany(Schema::class, 'model', 'model_type', 'model_key', 'slug')
            ->orderBy('position');
    }
}
