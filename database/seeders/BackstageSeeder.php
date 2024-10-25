<?php

namespace Vormkracht10\Backstage\Database\Seeders;

use Illuminate\Database\Seeder;
use Vormkracht10\Backstage\Database\Seeders\UserSeeder;

class BackstageSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ContentSeeder::class,
            DomainSeeder::class,
            FieldSeeder::class,
            LanguageSeeder::class,
            SiteSeeder::class,
            TypeSeeder::class,
            UserSeeder::class,
        ]);
    }
}
