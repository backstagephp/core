# Welcome Backstage

![Test](https://github.com/vormkracht10/backstage/actions/workflows/run-tests.yml/badge.svg)
![Fresh Laravel install](https://github.com/vormkracht10/backstage/actions/workflows/setup-in-laravel.yml/badge.svg)
![PHPStan](https://github.com/vormkracht10/backstage/actions/workflows/phpstan.yml/badge.svg)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/vormkracht10/backstage.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/backstage)
[![Total Downloads](https://img.shields.io/packagist/dt/vormkracht10/backstage.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/backstage)

Backstage is the CMS for building the modern web. Made with Laravel and Filament.

## Installation

You can install the package via composer in your (new) [Laravel app](https://laravel.com/docs/11.x#creating-a-laravel-project):

```bash
composer require vormkracht10/backstage
```

To get started quickly, use the backstage:install command:

```bash
php artisan backstage:install
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

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="backstage-views"
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Mark van Eijk](https://github.com/markvaneijk)
- [Bas van Dinther](https://github.com/baspa)
- [Mathieu de Ruiter](https://github.com/casmo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
