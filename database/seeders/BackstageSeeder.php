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
            FieldSeeder::class,
            SiteSeeder::class,
            LanguageSeeder::class,
            TypeSeeder::class,
            BlockSeeder::class,
            ContentSeeder::class,
            DomainSeeder::class,
            UserSeeder::class,
        ]);
    }
}
