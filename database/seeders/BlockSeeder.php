<?php

namespace Vormkracht10\Backstage\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Vormkracht10\Backstage\Models\Field;
use Vormkracht10\Backstage\Models\Block;

class BlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Block::factory()->state([
            'name' => $name = 'Text',
            'slug' => Str::slug($name),
            'icon' => 'document-text',
            'name_field' => null,
        ])
        ->has(Field::factory(1, [
            'name' => 'Body',
            'slug' => 'body',
            'field_type' => 'rich-editor',
            'position' => 1,
        ]))
        ->create();
    }
}
