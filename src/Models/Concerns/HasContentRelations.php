<?php

namespace Backstage\Models\Concerns;

use Backstage\Models\Content;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasContentRelations
{
    public function content(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'relationables', 'related_ulid', 'relation_ulid')
            ->where('related_type', $this->getMorphClass())
            ->where('relation_type', 'content');
    }
} 