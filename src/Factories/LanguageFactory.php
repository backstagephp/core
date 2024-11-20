<?php

namespace Vormkracht10\Backstage\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Vormkracht10\Backstage\Models\Language;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Language>
 */
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
        return [];
    }
}
