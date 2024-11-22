<?php

namespace Vormkracht10\Backstage\Factories;

use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Type>
 */
class TypeFactory extends Factory
{
    protected $model = Type::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [];
    }

    public function configure()
    {
        return $this->afterCreating(function (Type $type) {
            $type->sites()->attach(Site::default());
        });
    }
}
