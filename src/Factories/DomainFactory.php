<?php

namespace Vormkracht10\Backstage\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Vormkracht10\Backstage\Models\Domain;

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
            'name' => 'localhost',
            'environment' => 'local',
        ];
    }
}
