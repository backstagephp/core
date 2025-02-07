<?php

use Backstage\Models\Content;
use Backstage\Models\Site;

test('confirm public content is found', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory()
            ->withDomain('localhost'),
        'path' => 'welcome',
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => []]);

    expect($content->url)->toBe('https://localhost/welcome');

    $this->get($content->url)
        ->assertOk()
        ->assertSee($content->title);
});

test('confirm public content with site path is found', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory([
            'path' => 'site',
        ])
            ->withDomain('localhost'),
        'path' => 'news',
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => []]);

    expect($content->url)->toBe('https://localhost/site/news');

    $this->get($content->url)
        ->assertOk()
        ->assertSee($content->title);
});
