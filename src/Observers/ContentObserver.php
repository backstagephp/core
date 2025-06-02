<?php

namespace Backstage\Observers;

use Backstage\Models\Content;
use Backstage\Redirects\Laravel\Events\UrlHasChanged;

class ContentObserver
{
    public function saved(Content $content)
    {
        if ($content->isDirty('path') && $oldPath = $content->getOriginal('path') && $content->public) {

            $oldUrl = rtrim($content->pathPrefix . $oldPath, '/');

            if ($content->site->trailing_slash) {
                $oldUrl .= '/';
            }

            event(new UrlHasChanged(
                oldUrl: $oldUrl,
                newUrl: $content->url,
                code: 301
            ));
        }
    }
}
