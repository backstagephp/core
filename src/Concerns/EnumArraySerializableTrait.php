<?php

namespace Vormkracht10\Backstage\Concerns;

trait EnumArraySerializableTrait
{
    use EnumNamesTrait;
    use EnumValuesTrait;

    public static function array(): array
    {
        return array_combine(static::values(), static::names());
    }
}