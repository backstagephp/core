<?php

namespace Backstage\Models;

use Backstage\Media\Models\Media as BaseMedia;
use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends BaseMedia
{
    use HasPackageFactory;

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Discuss how we can optimize this relation (edits)
     */
    public function edits(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(
            ContentFieldValue::class,
            'model',
            'media_relationships',
            'media_ulid',
            'model_id'
        )
            ->withPivot(['meta', 'position'])
            ->withTimestamps();
    }

    public function getMimeTypeAttribute(): ?string
    {
        return $this->attributes['mime_type'] ?? null;
    }

    public function getEditAttribute(): ?array
    {
        $edit = $this->edits()->first();

        if (! $edit || ! $edit->pivot || ! $edit->pivot->meta) {
            return null;
        }

        return is_string($edit->pivot->meta)
            ? json_decode($edit->pivot->meta, true)
            : $edit->pivot->meta;
    }
}
