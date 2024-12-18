<?php

namespace Vormkracht10\Backstage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Models\ContentFieldValue;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Models\Site;

class ContentFieldValueFactory extends Factory
{
    protected $model = ContentFieldValue::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'value' => 'Spotlights are on.'
        ];
    }
}
