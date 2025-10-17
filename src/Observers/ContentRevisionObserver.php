<?php

namespace Backstage\Observers;

use Backstage\Models\Content;

class ContentRevisionObserver
{
    public function saved(Content $content)
    {
        $content->versions()->create([
            'data' => [
                'fields' => $content->getFormattedFieldValues(),
                'meta_tags' => $content->meta_tags,
            ],
        ]);
    }
}
