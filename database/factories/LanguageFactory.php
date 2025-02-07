<?php

namespace Backstage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Backstage\Models\Language;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->languageCode(),
            'name' => $this->faker->unique()->country(),
            'native' => $this->faker->unique()->country(),
            'active' => true,
            'default' => false,
        ];
    }
}
