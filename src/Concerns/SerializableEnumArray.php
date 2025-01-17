<?php

namespace Vormkracht10\Backstage\Concerns;

trait SerializableEnumArray
{
    use HasEnumNames;
    use HasEnumValues;

    public static function array(): array
    {
        return array_combine(static::values(), static::names());
    }
}
