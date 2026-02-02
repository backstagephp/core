<?php

namespace Backstage\Database\Factories;

use Backstage\Models\Language;
use Backstage\Models\Menu;
use Backstage\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(),
            'name' => $this->faker->words(2, true),
            'site_ulid' => Site::factory(),
            'language_code' => Language::factory(),
        ];
    }
}
