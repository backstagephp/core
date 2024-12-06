<?php

namespace Vormkracht10\Backstage;

use Illuminate\Support\Str;

class Backstage
{
    private static array $components = [
        'default' => '\Vormkracht10\Backstage\View\Components\DefaultBlock',
    ];

    public static function registerComponent(string $name, string $component = null): void
    {
        if (empty($component)) {
            $component = $name;
            $name = Str::snake(Str::replaceLast('Component', '', class_basename($component)), '-');
        }
        
        static::$components[$name] = $component;
    }

    public static function getComponents(): array
    {
        return static::$components;
    }

    public static function getComponentOptions()
    {
        return collect(static::$components)
            ->mapWithKeys(fn ($component, $name) => [$name => Str::headline(last(explode('\\', $component)))])
            ->sort();
    }

    public static function resolveComponent($slug)
    {
        return static::$components[$slug] ?? static::$components['default'];
    }
}
