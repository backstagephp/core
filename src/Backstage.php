<?php

namespace Vormkracht10\Backstage;

class Backstage {

    static private array $components = [];

    static public function registerComponent(string $name, string $component): void
    {
        static::$components[$name] = $component;
    }

    static public function getComponents(): array
    {
        return static::$components;
    }
}
