---
title: Quick start
---

# Welcome, Backstage

The CMS for Laravel developers.

### Quick install

You can install backstage in any (new) Laravel project or start from scratch. If you already have a (Laravel) website running you can skip step 1.

#### Step 1. (Optional) Create a new Laravel app.

```bash
laravel new my-website
```

#### Step 2. Require backstage/cms

```bash
composer require backstage/cms
```

Note: For now you may have to update composer.json to:
```json
    "repositories": {
        "laravel-redirects": {
            "type": "vcs",
            "url": "git@github.com:backstagephp/laravel-redirects.git"
        },
        "filament-redirects": {
            "type": "vcs",
            "url": "git@github.com:backstagephp/redirects.git"
        },
        "backstage/media": {
            "type": "vcs",
            "url": "git@github.com:backstagephp/media.git"
        },
        "backstage/fields": {
            "type": "vcs",
            "url": "git@github.com:backstagephp/fields.git"
        },
        "backstage": {
            "type": "vcs",
            "url": "git@github.com:backstagephp/core.git"
        }
    },
    "minimum-stability": "dev",
```

#### Step 3. Run migrations and add default settings and content

```bash
php artisan backstage:install
```

#### Step 4. (Optional) Remove or comment the default Laravel routes:

```php
// routes/web.php

// Route::get('/', function () {
//    return view('welcome');
//});
```

### Advanced setup

You can publish the migrations with:

```bash
php artisan vendor:publish --tag="backstage-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="backstage-config"
```