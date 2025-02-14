<?php

use Backstage\Models\Content;
use Backstage\Models\Site;

test('confirm home url works', function () {
    $content = Content::factory()->create([
        'path' => '',
        'public' => true,
    ]);

    expect($content->url)->toBe('');
});

test('confirm home url with trailing slash works', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory([
            'trailing_slash' => true,
        ]),
        'path' => '',
        'public' => true,
    ]);

    expect($content->url)->toBe('/');
});

test('confirm basic path works with trailing slash works', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory([
            'trailing_slash' => true,
        ]),
        'path' => 'welcome',
        'public' => true,
    ]);

    expect($content->url)->toBe('/welcome/');
});

test('confirm basic path works', function () {
    $content = Content::factory()->create([
        'path' => 'welcome',
        'public' => true,
    ]);

    expect($content->url)->toBe('/welcome');
});

test('confirm site domain works', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory()
            ->withDomain('example.com'),
        'path' => '',
        'public' => true,
    ]);

    expect($content->url)->toBe('https://example.com');
});

test('confirm site domain with path works', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory()
            ->withDomain('example.com'),
        'path' => 'welcome',
        'public' => true,
    ]);

    expect($content->url)->toBe('https://example.com/welcome');
});

test('confirm site domain with path and trailing slash works', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory([
            'trailing_slash' => true,
        ])
            ->withDomain('example.com'),
        'path' => 'welcome',
        'public' => true,
    ]);

    expect($content->url)->toBe('https://example.com/welcome/');
});

test('confirm site domain with language path works', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory()
            ->withDomain('example.com'),
        'path' => 'welcome',
        'public' => true,
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => ['path' => 'en']]);

    expect($content->url)->toBe('https://example.com/en/welcome');
});

test('confirm site with path works', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory([
            'path' => 'backstage',
        ])
            ->withDomain('example.com'),
        'path' => 'welcome',
        'public' => true,
    ]);

    expect($content->url)->toBe('https://example.com/backstage/welcome');
});

test('confirm site and language with path works', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory([
            'path' => 'backstage',
        ])
            ->withDomain('example.com'),
        'path' => 'welcome',
        'public' => true,
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => ['path' => 'en']]);

    expect($content->url)->toBe('https://example.com/backstage/en/welcome');
});
