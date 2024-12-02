<?php

namespace Vormkracht10\Backstage\Shared;

use Vormkracht10\Backstage\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTags
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables', 'taggable_ulid', 'tag_ulid');
    }
}
