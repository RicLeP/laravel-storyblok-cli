# Laravel Storyblok - Artisan CLI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/riclep/laravel-storyblok-cli.svg?style=flat-square)](https://packagist.org/packages/riclep/laravel-storyblok-cli)
[![Total Downloads](https://img.shields.io/packagist/dt/riclep/laravel-storyblok-cli.svg?style=flat-square)](https://packagist.org/packages/riclep/laravel-storyblok-cli)
![GitHub Actions](https://github.com/riclep/laravel-storyblok-cli/actions/workflows/main.yml/badge.svg)

Artisan commands for working with the Storyblok API in Laravel.

## Installation

You can install the package via composer:

```bash
composer require riclep/laravel-storyblok-cli
```

## Usage

### Setting variables environment
In the `.env` file of your Laravel application, you have to define the environment parameter for setting the Storyblok user Personal access token. You can obtain your Storyblok Personal access token [here](https://app.storyblok.com/#/me/account?tab=token).
Then you can define also the default space id for the space you want to manage via the Laravel CLI.
In the `.env` file of your Laravel project add these two parameters:

```
STORYBLOK_OAUTH_TOKEN=yourpersonalaccesstoken
STORYBLOK_SPACE_ID=yourspaceid
```


### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email ric@sirric.co.uk instead of using the issue tracker.

## Credits

-   [Richard Le Poidevin](https://github.com/riclep)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
