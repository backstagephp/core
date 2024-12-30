<?php

use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Models\Site;

test('confirm public content is found', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory()
            ->withDomain('localhost'),
        'path' => 'welcome',
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => []]);

    $this->get($content->url)
        ->assertOk()
        ->assertSee($content->title);
});
