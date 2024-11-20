<?php

namespace Vormkracht10\Backstage\Factories;

use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Models\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Site>
 */
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
            'site_ulid' => Site::default(),
            'name' => 'localhost',
            'environment' => 'local',
        ];
    }
}
