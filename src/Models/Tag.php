<?php

namespace Backstage\Models;

use Backstage\Shared\HasPackageFactory;
use Backstage\Translations\Laravel\Contracts\TranslatesAttributes;
use Backstage\Translations\Laravel\Models\Concerns\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model implements TranslatesAttributes
{
    use HasTranslatableAttributes;
    use HasPackageFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $guarded = [];

    public function content(): MorphToMany
    {
        return $this->morphedByMany(Content::class, 'taggable', 'taggables', 'tag_ulid', 'taggable_ulid');
    }

    public function sites(): MorphToMany
    {
        return $this->morphedByMany(Site::class, 'taggable', 'taggables', 'tag_ulid', 'taggable_ulid');
    }

    public function getTranslatableAttributes(): array
    {
        return [
            'name',
            'slug'
        ];
    }
}
