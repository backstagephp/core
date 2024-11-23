<?php

namespace Vormkracht10\Backstage\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Vormkracht10\Backstage\Models\Type;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $type = Type::factory()->state([
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
