<?php

namespace Backstage\Database\Seeders;

use Backstage\Models\Site;
use Backstage\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->state([
            'name' => 'Mark',
            'email' => 'mark@ux.nl',
            'password' => 'mark@ux.nl',
        ])->create();

        User::factory([
            'name' => 'Manoj',
            'email' => 'manoj@ux.nl',
            'password' => 'manoj@ux.nl',
        ])->create();

        User::factory()->state([
            'name' => 'Mathieu',
            'email' => 'mathieu@ux.nl',
            'password' => 'mathieu@ux.nl',
        ])->create();

        User::factory()->state([
            'name' => 'Bas',
            'email' => 'bas@ux.nl',
            'password' => 'bas@ux.nl',
        ])->create();

        User::factory([
            'name' => 'Yoni',
            'email' => 'yoni@ux.nl',
            'password' => 'yoni@ux.nl',
        ])->create();

        User::factory([
            'name' => 'Patrick',
            'email' => 'patrick@ux.nl',
            'password' => 'patrick@ux.nl',
        ])->create();

        User::factory([
            'name' => 'Sandro',
            'email' => 'sandro@ux.nl',
            'password' => 'sandro@ux.nl',
        ])->create();

        Site::default()->users()->attach(User::all());
    }
}
