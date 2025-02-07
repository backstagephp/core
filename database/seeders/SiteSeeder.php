<?php

namespace Backstage\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Backstage\Models\Site;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Site::factory()->state([
            'name' => $name = 'Vormkracht10',
            'slug' => Str::slug($name),
            'timezone' => 'Europe/Amsterdam',
            'default' => true,
        ])->create();
    }
}
