<?php

namespace Backstage\Database\Factories;

use Backstage\Models\Domain;
use Backstage\Translations\Laravel\Models\Language;
use Backstage\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class DomainFactory extends Factory
{
    protected $model = Domain::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_ulid' => Site::factory(),
            'name' => $this->faker->domainName(),
            'environment' => 'local',
        ];
    }

    public function withLanguage(): self
    {
        return $this->has(Language::factory());
    }
}
