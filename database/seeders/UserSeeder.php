<?php

namespace Backstage\Database\Seeders;

use Illuminate\Database\Seeder;
use Backstage\Models\Site;
use Backstage\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->state([
            'name' => 'Mark',
            'email' => 'mark@vk10.nl',
            'password' => 'mark@vk10.nl',
        ])->create();

        User::factory()->state([
            'name' => 'Mathieu',
            'email' => 'mathieu@vk10.nl',
            'password' => 'mathieu@vk10.nl',
        ])->create();

        User::factory()->state([
            'name' => 'Bas',
            'email' => 'bas@vk10.nl',
            'password' => 'bas@vk10.nl',
        ])->create();

        Site::default()->users()->attach(User::all());
    }
}
