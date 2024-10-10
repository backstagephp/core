<?php

namespace Vormkracht10\Backstage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vormkracht10\Backstage\Backstage
 */
class Backstage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Vormkracht10\Backstage\Backstage::class;
    }
}
