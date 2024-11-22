<?php

namespace Vormkracht10\Backstage\Factories;

use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Vormkracht10\Backstage\Models\Content;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Site>
 */
class ContentFactory extends Factory
{
    protected $model = Content::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_ulid' => Site::default(),
            'language_code' => Language::first(),
            'type_slug' => 'page',
            'title' => 'Home',
            'name' => 'Home',
            'slug' => 'home',
            'path' => '/',
        ];
    }
}
