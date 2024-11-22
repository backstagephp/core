<?php

namespace Vormkracht10\Backstage\Factories;

use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

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

    public function configure()
    {
        return $this->afterCreating(function (Language $language) {
            $language->sites()->attach(Site::default());
        });
    }
}
