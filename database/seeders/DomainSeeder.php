<?php

namespace Backstage\Database\Seeders;

use Illuminate\Database\Seeder;
use Backstage\Models\Domain;

class DomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Domain::factory()->create();
    }
}
