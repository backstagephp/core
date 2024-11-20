<?php

namespace Vormkracht10\Backstage\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Vormkracht10\Backstage\Models\Type;

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
}
