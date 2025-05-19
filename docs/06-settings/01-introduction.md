---
title: Settings
---

# Settings

A setting is a collection of one or more setting values.

## Setup

1. Create a setting in Settings, for example "Address" as name.
2. Next, add fields that hold values for those settings. E.g. A field for "Street", "Zipcode" and "City".

## Get settings

Settings can be retreived by the `setting($key, $default = null)` helper.

```php
echo setting('address.zipcode');
```

Or in your blade files by:

```
{{ setting('address.zipcode') }}
```

## Default value

You can provide a default value as second parameter.

```php
echo setting('address.country', 'The Netherlands');
```

## Getting all fields

If no key is provided, all values will be returned.

```php
$settings = setting('address');
echo $settings['street'];
```