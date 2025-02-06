<?php

namespace Backstage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Backstage\Models\Block;
use Backstage\Models\Site;

class BlockFactory extends Factory
{
    protected $model = Block::class;

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
        return $this->afterCreating(function (Block $block) {
            $block->sites()->attach(Site::default());
        });
    }
}
