<?php

use Backstage\Models\Setting;

test('confirm setting returns correct value', function () {
    Setting::factory()->create([
        'name' => 'address',
        'slug' => 'address',
        'values' => [
            'street' => $street = 'St. Annastraat',
            'city' => $city = 'Nijmegen',
        ],
    ]);

    expect(setting('address.street'))->toBe($street);
});

test('confirm setting returns array on setting', function () {

    Setting::factory()->create([
        'name' => 'address',
        'slug' => 'address',
        'values' => [
            'street' => $street = 'St. Annastraat',
            'city' => $city = 'Nijmegen',
        ],
    ]);

    expect(setting('address'))->toMatchArray([
        'street' => $street,
        'city' => $city,
    ]);
});

test('confirm setting returns default value', function () {
    expect(setting('address.street', 'default'))->toBe('default');
});

test('confirm setting returns null', function () {
    expect(setting('address.street'))->toBeNull();
});
