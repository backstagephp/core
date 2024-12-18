<?php

namespace Vormkracht10\Backstage\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Vormkracht10\Backstage\Models\Block;
use Vormkracht10\Backstage\Models\Field;
use Vormkracht10\Backstage\Models\Form;
use Vormkracht10\Backstage\Models\FormAction;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Form::factory()->state([
            'name' => $name = 'Contact',
            'slug' => Str::slug($name),
            'name_field' => null,
        ])
            ->has(Field::factory(1, [
                'name' => 'Name',
                'slug' => 'name',
                'field_type' => 'text',
                'position' => 1,
            ]))
            ->has(Field::factory(1, [
                'name' => 'Email',
                'slug' => 'email',
                'field_type' => 'text',
                'position' => 2,
            ]))
            ->has(FormAction::factory(1, [
                'config' => [
                    'subject' => 'Contact submission',
                    'to_email' => 'email',
                    'to_name' => 'name',
                    'from_email' => config('mail.from.address', 'contact@vormkracht10.nl'),
                    'from_name' => config('mail.from.name', 'Vormkracht10'),
                    'body' => 'Thanks for contacting us.'
                ]
            ]))
            ->create();
    }
}
