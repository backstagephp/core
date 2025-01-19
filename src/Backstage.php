<?php

namespace Vormkracht10\Backstage;

use Illuminate\Support\Str;
use Vormkracht10\Backstage\Models\Block;

class Backstage
{
    private static array $components = [];

    private static array $cachedBlocks = [
        'default' => '\Vormkracht10\Backstage\View\Components\DefaultBlock',
    ];

    public static function registerComponent(string $name, ?string $component = null): void
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
        if (self::$cachedBlocks[$slug] ?? false) {
            return self::$cachedBlocks[$slug];
        }

        $block = Block::select('component')->where('slug', $slug)->first();

        if (! $block) {
            return self::$cachedBlocks[$slug] = static::$cachedBlocks['default'];
        }

        return self::$cachedBlocks[$slug] = static::$components[$block->component] ?? static::$cachedBlocks['default'];
    }
}
