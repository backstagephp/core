<?php

namespace Vormkracht10\Backstage;

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
}
