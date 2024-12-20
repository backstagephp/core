<?php

namespace Vormkracht10\Backstage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Vormkracht10\Backstage\Models\FormAction;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Models\Site;

class FormActionFactory extends Factory
{
    protected $model = FormAction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'language_code' => Language::default(),
            'site_ulid' => Site::default(),
            'type' => 'email',
            'config' => [
                'subject' => 'Contact submission',
                'to_email' => 'email',
                'to_name' => 'name',
                'body' => 'Thanks for your submission.',
            ],
        ];

    }
}
