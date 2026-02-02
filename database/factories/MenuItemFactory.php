<?php

namespace Backstage\Database\Factories;

use Backstage\Models\Menu;
use Backstage\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_slug' => Menu::factory(),
            'name' => 'Home',
            'slug' => 'home',
            'title' => 'Home',
            'active' => true,
            'url' => '/',
            'target' => '_self',
            'position' => 0,
        ];
    }
}
