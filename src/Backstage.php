<?php

namespace Vormkracht10\Backstage;

use Illuminate\Support\Str;

class Backstage
{
    private static array $components = [];

    public static function registerComponent(string $name, string $component): void
    {
        static::$components[$name] = $component;
    }

    public static function getComponents(): array
    {
        return static::$components;
    }

    public static function getComponentOptions()
    {
        return collect(static::$components)
            ->mapWithKeys(fn ($component, $name) => [$name => Str::title(last(explode('\\', $component)))])
            ->sort();
    }
}
