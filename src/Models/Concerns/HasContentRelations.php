<?php

namespace Backstage\Models\Concerns;

use Backstage\Models\ContentRelation;
use Backstage\Models\Content;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasContentRelations
{
    public function asRelation(): HasMany
    {
        return $this->hasMany(ContentRelation::class, 'relation_ulid', 'ulid')
            ->where('relation_type', $this->getMorphClass());
    }

    public function asRelated(): HasMany
    {
        return $this->hasMany(ContentRelation::class, 'related_ulid', 'ulid')
            ->where('related_type', $this->getMorphClass());
    }

    public function getRelatedOfType(string $type, string $direction = 'relation'): Collection
    {
        $query = $direction === 'relation' 
            ? $this->asRelation()->where('related_type', $type)
            : $this->asRelated()->where('relation_type', $type);

        $relations = $query->get();

        return $relations->map(function ($relation) use ($direction) {
            return $direction === 'relation'
                ? $relation->related
                : $relation->relation;
        });
    }

    public function addRelation($target): void
    {
        $this->asRelation()->create([
            'related_type' => $target->getMorphClass(),
            'related_ulid' => $target->ulid,
        ]);
    }

    public function removeRelation($target): void
    {
        $this->asRelation()
            ->where('related_type', $target->getMorphClass())
            ->where('related_ulid', $target->ulid)
            ->delete();
    }

    public function relatedContents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'relationables', 'relation_ulid', 'related_ulid')
            ->where('relation_type', $this->getMorphClass());
    }

    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'relationables', 'related_ulid', 'relation_ulid')
            ->where('related_type', $this->getMorphClass())
            ->where('relation_type', 'content');
    }
} 