<?php

namespace Vormkracht10\Backstage\Enums;

use Vormkracht10\Backstage\Concerns\EnumArraySerializableTrait;

enum ColorFormat: string
{
    use EnumArraySerializableTrait;

    case HEX = 'hex';
    case RGB = 'rgb';
    case RGBA = 'rgba';
    case HSL = 'hsl';
}
