<?php

namespace Vormkracht10\Backstage\Database\Seeders;

use Illuminate\Database\Seeder;

class BackstageSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ContentSeeder::class,
            FieldSeeder::class,
            LanguageSeeder::class,
            SiteSeeder::class,
            DomainSeeder::class,
            TypeSeeder::class,
            UserSeeder::class,
        ]);
    }
}
