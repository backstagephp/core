<?php

namespace Backstage\Models\Concerns;

use Backstage\Models\ContentRelation;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait HasContentRelations
{
    public function outgoingRelations(): HasMany
    {
        return $this->hasMany(ContentRelation::class, 'source_ulid', 'ulid')
            ->where('source_type', $this->getMorphClass());
    }

    public function incomingRelations(): HasMany
    {
        return $this->hasMany(ContentRelation::class, 'target_ulid', 'ulid')
            ->where('target_type', $this->getMorphClass());
    }

    public function getRelatedOfType(string $type, string $direction = 'outgoing'): Collection
    {
        $query = $direction === 'outgoing' 
            ? $this->outgoingRelations()->where('target_type', $type)
            : $this->incomingRelations()->where('source_type', $type);

        $relations = $query->get();

        return $relations->map(function ($relation) use ($direction) {
            return $direction === 'outgoing'
                ? $relation->target
                : $relation->source;
        });
    }

    public function addRelation($target): void
    {
        $this->outgoingRelations()->create([
            'target_type' => $target->getMorphClass(),
            'target_ulid' => $target->ulid,
        ]);
    }

    public function removeRelation($target): void
    {
        $this->outgoingRelations()
            ->where('target_type', $target->getMorphClass())
            ->where('target_ulid', $target->ulid)
            ->delete();
    }
} 