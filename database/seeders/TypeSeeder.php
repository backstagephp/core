<?php

namespace Vormkracht10\Backstage\Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Vormkracht10\Backstage\Models\Type;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Type::factory()->state([
            'name' => $name = 'Page',
            'name_plural' => Str::plural($name),
            'slug' => Str::slug($name),
            'icon' => 'document',
            'title_field' => 'title',
            'body_field' => 'body',
            'public' => true,
        ])->create();
    }
}
