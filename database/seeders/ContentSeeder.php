<?php

namespace Vormkracht10\Backstage\Database\Seeders;

use Illuminate\Database\Seeder;
use Vormkracht10\Backstage\Models\Content;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Content::factory()->create();
    }
}
