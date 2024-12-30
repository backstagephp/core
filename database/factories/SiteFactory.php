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
            'title' => $this->faker->sentence(),
            'title_separator' => '|',
            'primary_color' => $this->faker->hexColor(),
            'timezone' => $this->faker->timezone(),
            'auth' => false,
            'default' => false,
            'trailing_slash' => false,
        ];
    }

    public function withDomain($domain = 'localhost'): self
    {
        return $this->afterCreating(function (Site $site) use ($domain) {
            $site->domains()->create([
                'name' => $domain,
            ]);
        });
    }
}
