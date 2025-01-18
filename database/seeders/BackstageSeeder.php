<?php

namespace Vormkracht10\Backstage\Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Vormkracht10\Fields\Models\Field;
use Vormkracht10\Backstage\Models\Form;
use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Models\Type;
use Vormkracht10\Backstage\Models\User;
use Vormkracht10\Backstage\Models\Block;
use Vormkracht10\Backstage\Models\Domain;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Models\FormAction;
use Vormkracht10\Backstage\Models\ContentFieldValue;

class BackstageSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create language
        $language = Language::factory([
            'name' => 'English',
            'native' => 'English',
            'code' => 'en',
            'default' => true,
        ])->create();

        $site = Site::factory([
            'name' => $name = config('app.name', 'Backstage'),
            'slug' => Str::slug($name),
            'title' => $name,
            'default' => true,
        ])
            ->create();

        $domain = Domain::factory([
            'site_ulid' => $site->ulid,
            'name' => pathinfo(config('app.url', 'localhost'))['basename'],
        ])
            ->hasAttached($language, ['path' => ''])
            ->create();

        $type = Type::factory()->state([
            'name' => $name = 'Page',
            'name_plural' => Str::plural($name),
            'slug' => Str::slug($name),
            'icon' => 'document',
            'name_field' => 'title',
            'body_field' => 'body',
            'public' => true,
        ])
            ->has(Field::factory(1, [
                'name' => 'Title',
                'slug' => 'title',
                'field_type' => 'text',
                'position' => 1,
            ]))
            ->has(Field::factory(1, [
                'name' => 'Body',
                'slug' => 'body',
                'field_type' => 'rich-editor',
                'position' => 2,
            ]))
            ->has(Field::factory(1, [
                'name' => 'Blocks',
                'slug' => 'blocks',
                'field_type' => 'builder',
                'position' => 3,
            ]))
            ->hasAttached($site)
            ->create();

        Form::factory([
            'name' => $name = 'Contact',
            'slug' => Str::slug($name),
            'name_field' => null,
            'site_ulid' => $site->ulid,
        ])
            ->for($site)
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
                'langauge_code' => $language,
                'config' => [
                    'subject' => 'Contact submission',
                    'to_email' => 'email',
                    'to_name' => 'name',
                    'from_email' => config('mail.from.address', 'contact@vormkracht10.nl'),
                    'from_name' => config('mail.from.name', 'Vormkracht10'),
                    'body' => 'Thanks for contacting us.',
                ],
            ]));

        Block::factory([
            'name' => $name = 'Text',
            'slug' => Str::slug($name),
            'icon' => 'document-text',
            'name_field' => null,
        ])
            ->has(Field::factory(1, [
                'name' => 'Body',
                'slug' => 'body',
                'field_type' => 'rich-editor',
                'position' => 1,
            ]))
            ->create();

        Block::factory()->state([
            'name' => $name = 'Form',
            'slug' => Str::slug($name),
            'icon' => 'document-text',
            'name_field' => null,
            'component' => 'form',
        ])
            ->has(Field::factory(1, [
                'name' => 'Slug',
                'slug' => 'slug',
                'field_type' => 'text',
                'position' => 1,
            ]))
            ->create();

        Content::factory([
            'type_slug' => $type->slug,
            'site_ulid' => $site->ulid,
            'language_code' => $language->code,
        ])
            ->has(ContentFieldValue::factory(1, [
                'field_ulid' => Field::where('slug', 'title')->where('model_type', 'type')->where('model_key', 'page')->first()?->ulid,
                'value' => 'Home',
            ]), 'values')
            ->has(ContentFieldValue::factory(1, [
                'field_ulid' => Field::where('slug', 'body')->where('model_type', 'type')->where('model_key', 'page')->first()?->ulid,
                'value' => '<p>Welcome Backstage!</p><p>Open <a href="/backstage">/backstage</a> to begin.</p>',
            ]), 'values')
            ->create();

        // Contact
        Content::factory([
            'name' => 'Contact',
            'slug' => 'contact',
            'path' => 'contact',
            'type_slug' => $type->slug,
            'site_ulid' => $site->ulid,
            'language_code' => $language->code,
            'meta_tags' => ['title' => 'Contact'],
        ])
            ->has(ContentFieldValue::factory(1, [
                'field_ulid' => Field::where('slug', 'title')->where('model_type', 'type')->where('model_key', 'page')->first()?->ulid,
                'value' => 'Contact',
            ]), 'values')
            ->has(ContentFieldValue::factory(1, [
                'field_ulid' => Field::where('slug', 'blocks')->where('model_type', 'type')->where('model_key', 'page')->first()?->ulid,
                'value' => json_encode([
                    (string) Str::uuid() => ['type' => 'form', 'data' => ['slug' => 'contact']],
                ]),
            ]), 'values')->create();

        User::factory([
            'name' => 'Mark',
            'email' => 'mark@vk10.nl',
            'password' => 'mark@vk10.nl',
        ])
            ->create();

        User::factory([
            'name' => 'Mathieu',
            'email' => 'mathieu@vk10.nl',
            'password' => 'mathieu@vk10.nl',
        ])
            ->create();

        User::factory([
            'name' => 'Bas',
            'email' => 'bas@vk10.nl',
            'password' => 'bas@vk10.nl',
        ])
            ->create();
    }
}