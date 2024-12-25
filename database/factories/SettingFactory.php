<?php

namespace Vormkracht10\Backstage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Models\Setting;
use Vormkracht10\Backstage\Models\Site;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_ulid' => Site::factory(),
            'name' => fake()->name(),
            'slug' => fake()->slug(),
            'language_code' => Language::factory(),
            'values' => [
                fake()->slug() => fake()->name(),
                fake()->slug() => fake()->name(),
            ],
        ];
    }
}
