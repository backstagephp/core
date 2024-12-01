<?php

namespace Vormkracht10\Backstage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Models\Site;

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
