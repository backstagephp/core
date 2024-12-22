<?php

use Vormkracht10\Backstage\Models\Setting;

test('confirm setting returns value', function () {
    Setting::factory()->create([
        'name' => 'address',
        'slug' => 'address',
        'values' => [
            'street' => $street = 'St. Annastraat',
            'city' => $city = 'Nijmegen',
        ],
    ]);

    // Single value
    expect(setting('address.street'))->toBe($street);

    // All setting values
    expect(setting('address'))->toBe([
        'street' => $street,
        'city' => $city,
    ]);

    // Default value
    expect(setting('address.zipcode', $zipcode = '1234AB'))->toBe($zipcode);
});
