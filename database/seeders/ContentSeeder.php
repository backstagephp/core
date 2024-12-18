<?php

namespace Vormkracht10\Backstage\Database\Seeders;

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Lazy\LazyUuidFromString;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Models\ContentFieldValue;
use Vormkracht10\Backstage\Models\Field;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Content::factory()
            ->create();

        Content::factory()
            ->state([
                'name' => 'Contact',
                'slug' => 'contact',
                'path' => '/contact',
                'meta_tags' => ['title' => 'Contact'],
            ])
            ->has(ContentFieldValue::factory(1, [
                'field_ulid' => Field::where('slug', 'blocks')->where('model_type', 'type')->where('model_key', 'page')->first()?->ulid,
                'value' => json_encode([
                    (string) Str::uuid() => ['type' => 'form', 'data' => ['slug' => 'contact']]
                ])
            ]), 'values')->create();
    }
}
