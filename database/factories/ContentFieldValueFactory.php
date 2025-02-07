<?php

namespace Backstage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Backstage\Models\ContentFieldValue;

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
            'value' => 'Spotlights are on.',
        ];
    }
}
