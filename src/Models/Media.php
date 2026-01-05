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
        $mediaUlid = $this->ulid ?? 'UNKNOWN';
        
        if ($this->relationLoaded('edits')) {
            $edit = $this->edits->first();
            if (! $edit || ! $edit->relationLoaded('pivot') || ! $edit->pivot || ! $edit->pivot->meta) {
                \Log::info("[CROP DEBUG] Media::getEditAttribute (via edits relation) for {$mediaUlid}: NO META", [
                    'has_edit' => $edit !== null,
                    'has_pivot_relation' => $edit && $edit->relationLoaded('pivot'),
                    'has_pivot' => $edit && $edit->pivot !== null,
                    'has_meta' => $edit && $edit->pivot && $edit->pivot->meta !== null,
                ]);
                return null;
            }

            $result = is_string($edit->pivot->meta) ? json_decode($edit->pivot->meta, true) : $edit->pivot->meta;
            \Log::info("[CROP DEBUG] Media::getEditAttribute (via edits relation) for {$mediaUlid}: FOUND", [
                'has_crop' => is_array($result) && isset($result['crop']),
                'has_cdnUrlModifiers' => is_array($result) && isset($result['cdnUrlModifiers']),
            ]);
            return $result;
        }

        $edit = $this->edits()->first();
        if (! $edit || ! $edit->pivot || ! $edit->pivot->meta) {
            \Log::info("[CROP DEBUG] Media::getEditAttribute (via edits query) for {$mediaUlid}: NO META", [
                'has_edit' => $edit !== null,
                'has_pivot' => $edit && $edit->pivot !== null,
                'has_meta' => $edit && $edit->pivot && $edit->pivot->meta !== null,
            ]);
            return null;
        }

        $result = is_string($edit->pivot->meta) ? json_decode($edit->pivot->meta, true) : $edit->pivot->meta;
        \Log::info("[CROP DEBUG] Media::getEditAttribute (via edits query) for {$mediaUlid}: FOUND", [
            'has_crop' => is_array($result) && isset($result['crop']),
            'has_cdnUrlModifiers' => is_array($result) && isset($result['cdnUrlModifiers']),
        ]);
        return $result;
    }
}
