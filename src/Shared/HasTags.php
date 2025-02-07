<?php

namespace Backstage\Shared;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Backstage\Models\Tag;

trait HasTags
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables', 'taggable_ulid', 'tag_ulid');
    }
}
