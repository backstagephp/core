<?php

namespace Backstage\Media\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Backstage\Media\Media
 */
class Media extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Backstage\Media\Media::class;
    }
}
