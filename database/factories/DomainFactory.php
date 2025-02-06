<?php

namespace Backstage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Backstage\Models\Domain;
use Backstage\Models\Language;
use Backstage\Models\Site;

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
