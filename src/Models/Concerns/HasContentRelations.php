<?php

namespace Backstage\Models\Concerns;

use Backstage\Models\Content;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasContentRelations
{
    public function content(): MorphToMany
    {
        return $this->morphToMany(Content::class, 'related', 'relationables', 'related_ulid', 'relation_ulid')
            ->where('relation_type', 'content');
    }
} 