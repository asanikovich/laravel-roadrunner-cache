# Laravel RoadRunner Cache

[![Latest Version on Packagist](https://img.shields.io/packagist/v/asanikovich/laravel-roadrunner-cache.svg?style=flat-square)](https://packagist.org/packages/asanikovich/laravel-roadrunner-cache)
[![GitHub Tests Status](https://img.shields.io/github/actions/workflow/status/asanikovich/laravel-roadrunner-cache/tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/asanikovich/laravel-roadrunner-cache/actions/workflows/tests.yml?query=branch%3Amaster)
[![GitHub Tests Coverage Status](https://img.shields.io/codecov/c/github/asanikovich/laravel-roadrunner-cache?token=GXMKS36D91&style=flat-square)](https://github.com/asanikovich/laravel-roadrunner-cache/actions/workflows/tests.yml?query=branch%3Amaster)
[![GitHub Code Style Status](https://img.shields.io/github/actions/workflow/status/asanikovich/laravel-roadrunner-cache/phpstan.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/asanikovich/laravel-roadrunner-cache/actions/workflows/phpstan.yml?query=branch%3Amaster)
[![GitHub Lint Status](https://img.shields.io/github/actions/workflow/status/asanikovich/laravel-roadrunner-cache/pint.yml?branch=master&label=lint&style=flat-square)](https://github.com/asanikovich/laravel-roadrunner-cache/actions/workflows/pint.yml?query=branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/asanikovich/laravel-roadrunner-cache.svg?style=flat-square)](https://packagist.org/packages/asanikovich/laravel-roadrunner-cache)
[![Licence](https://img.shields.io/packagist/l/asanikovich/laravel-roadrunner-cache.svg?style=flat-square)](https://packagist.org/packages/asanikovich/laravel-roadrunner-cache)

**This Laravel package allows you to work with RoadRunner KV Cache in Laravel (as cache driver).**

## Getting Started

### Installing the Package

You can install the package via composer:

```bash
composer require asanikovich/laravel-roadrunner-cache
```

### Configuration

Make sure you have in your RoadRunner config file (.rr.yaml) next sections:
- RPC section 
- KV section

Full example of RoadRunner configuration file:
```yaml
version: '3'
rpc:
  listen: 'tcp://127.0.0.1:6001'
server:
  command: php /var/www/html/vendor/bin/roadrunner-worker
http:
  address: '127.0.0.1:8080'
  pool:
    num_workers: 1
kv:
    memory:
        driver: memory
        config:
            interval: 1
    boltdb:
        driver: boltdb
        config:
            interval: 1
```

Publish the config file and setup RPC connection:

```bash
php artisan vendor:publish --tag="laravel-roadrunner-cache-config"
```

Add to cache configuration file (/config/cache.php) new store with driver 'roadrunner': 
```php
<?php
    'default' => 'rr-memory', // Default store (optional)

    'stores' => [
        'rr-memory' => [ // Your custom store name
            'driver' => 'roadrunner',
            // section name from KV plugin settings in RoadRunner config file (.rr.yaml)
            'connection' => 'memory',
        ],
        'rr-boltdb' => [ // Your custom store name (another store is optional)
            'driver' => 'roadrunner',
            // section name from KV plugin settings in RoadRunner config file (.rr.yaml)
            'connection' => 'boltdb',
        ],
    ],
```

To use in your code:
```php
<?php
    use Illuminate\Support\Facades\Cache;

    Cache::driver()->get('key'); // Default main store - rr-memory
    Cache::driver('rr-boltdb')->get('key'); // rr-boltdb store
```

All done! 🚀

## Development
Here are some useful commands for development

Before running tests run db by docker-compose:
```bash
docker-compose up -d
```
Run tests:
```bash
composer run test
```
Run tests with coverage:
```bash
composer run test-coverage
```
Perform type checking:
```bash
composer run phpstan
```
Format your code:
```bash
composer run format
```

## Updates and Changes

For details on updates and changes, please refer to our [CHANGELOG](CHANGELOG.md).

## License

Laravel RoadRunner Cache is released under The MIT License (MIT). For more information, please see our [License File](LICENSE.md).
