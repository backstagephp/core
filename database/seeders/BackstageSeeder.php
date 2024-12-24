<?php

namespace Vormkracht10\Backstage\Database\Seeders;

use Illuminate\Database\Seeder;
use Vormkracht10\Backstage\Models\Site;

class BackstageSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $site = Site::factory([
            'name' => config('app.name'),
        ]);

        $this->call([
            FieldSeeder::class,
            SiteSeeder::class,
            LanguageSeeder::class,
            TypeSeeder::class,
            FormSeeder::class,
            BlockSeeder::class,
            ContentSeeder::class,
            DomainSeeder::class,
            UserSeeder::class,
        ]);
    }
}
