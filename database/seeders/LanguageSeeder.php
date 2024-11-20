<?php

namespace Vormkracht10\Backstage\Database\Seeders;

use Illuminate\Database\Seeder;
use Vormkracht10\Backstage\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Language::factory()->state([
            'code' => 'nl',
            'country_code' => 'nl',
            'hreflang' => 'nl-NL',
            'name' => 'Dutch',
            'native' => 'Nederlands',
            'default' => true,
        ])->create();
    }
}
