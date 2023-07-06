# Audit database changes in Laravel using database triggers.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/esign/laravel-database-auditing.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-database-auditing)
[![Total Downloads](https://img.shields.io/packagist/dt/esign/laravel-database-auditing.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-database-auditing)
![GitHub Actions](https://github.com/esign/laravel-database-auditing/actions/workflows/main.yml/badge.svg)

A short intro about the package.

## Installation

You can install the package via composer:

```bash
composer require esign/laravel-database-auditing
```

The package will automatically register a service provider.

Next up, you can publish the configuration file:
```bash
php artisan vendor:publish --provider="Esign\DatabaseAuditing\DatabaseAuditingServiceProvider" --tag="config"
```

## Usage

### Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
