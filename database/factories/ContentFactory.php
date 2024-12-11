<?php

namespace Vormkracht10\Backstage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Models\Site;

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
            'language_code' => Language::default(),
            'type_slug' => 'page',
            'name' => 'Home',
            'slug' => 'home',
            'path' => '/',
            'meta_tags' => ['title' => 'Home'],
            'edited_at' => now(),
        ];
    }
}
