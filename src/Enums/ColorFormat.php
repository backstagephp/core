<?php

namespace Vormkracht10\Backstage\Enums;

use Vormkracht10\Backstage\Concerns\SerializableEnumArray;

enum ColorFormat: string
{
    use SerializableEnumArray;

    case HEX = 'hex';
    case RGB = 'rgb';
    case RGBA = 'rgba';
    case HSL = 'hsl';
}