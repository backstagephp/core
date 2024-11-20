<?php

namespace Vormkracht10\Backstage\Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Vormkracht10\Backstage\Models\Site;

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
            'default_language_code' => 'nl',
            'default_country_code' => 'nl',
            'default' => true,
        ])->create();
    }
}
