<?php

namespace Vormkracht10\Backstage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Models\Type;

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
            'site_ulid' => Site::factory(),
            'language_code' => Language::factory(),
            'type_slug' => Type::factory(),
            'name' => 'Home',
            'slug' => 'home',
            'path' => '/',
            'meta_tags' => ['title' => 'Home'],
            'published_at' => now(),
            'edited_at' => now(),
        ];
    }
}
