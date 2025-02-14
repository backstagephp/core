<?php

namespace Backstage\Observers;

use Backstage\Models\Content;

class ContentDepthObserver
{
    public function saved(Content $content)
    {
        if ($content->isDirty('parent_ulid')) {
            dispatch(function () use ($content) {
                $content->depth = Content::tree()->depthFirst()->get()->where('ulid', $content->parent_ulid)->first()->depth ?: 0;
                $content->saveQuietly();
            });
        }
    }
}
