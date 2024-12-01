<?php

namespace Vormkracht10\Backstage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Vormkracht10\Backstage\Models\Site;

class SiteFactory extends Factory
{
    protected $model = Site::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $name = fake()->name(),
            'slug' => Str::slug($name),
            'timezone' => config('app.locale'),
            'primary_color' => '#ef4444',
        ];
    }
}
