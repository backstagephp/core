<?php

namespace Backstage\Models\Concerns;

use Backstage\Models\Content;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasContentRelations
{
    public function content(): MorphToMany
    {
        return $this->morphMany(Content::class, 'relationables', 'related_ulid', 'relation_ulid')
            ->where('related_type', $this->getMorphClass())
            ->where('relation_type', 'content');
    }
} 