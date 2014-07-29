PhpBuiltinServer [![Latest Stable](https://poser.pugx.org/tiger-seo/PhpBuiltinServer/version.png)](https://packagist.org/packages/tiger-seo/PhpBuiltinServer) [![Total Downloads](https://poser.pugx.org/tiger-seo/PhpBuiltinServer/downloads.png)](https://packagist.org/packages/tiger-seo/PhpBuiltinServer)
================

Codeception extension to start and stop PHP built-in web server for your tests.

| ------- | -------- | -------- |
| **Codeception 1.x** | **1.1.x** | [![Build Status](https://secure.travis-ci.org/tiger-seo/PhpBuiltinServer.png?branch=v1.1.x)](http://travis-ci.org/tiger-seo/PhpBuiltinServer)
| **Codeception 2.x** | **1.2.x** | [![Build Status](https://secure.travis-ci.org/tiger-seo/PhpBuiltinServer.png?branch=master)](http://travis-ci.org/tiger-seo/PhpBuiltinServer)

## Minimum requirements

* Codeception 1.6.4
* PHP 5.4

## Installation

1. Install [Codeception](http://codeception.com) via Composer
2. Add `codeception/phpbuiltinserver: "*"` to your `composer.json`
3. Run `composer install`
4. Include extensions into `codeception.yml` configuration:

## Configuration

### general example

``` yaml
paths:
    tests: .
    log: _log
    data: _data
    helpers: _helpers
extensions:
    enabled:
        - Codeception\Extension\PhpBuiltinServer
    config:
        Codeception\Extension\PhpBuiltinServer:
            hostname: localhost
            port: 8000
            documentRoot: _data
            startDelay: 1
            phpIni: /etc/php5/apache2/php.ini
```

### example for projects based on Symfony
``` yaml
paths:
    tests: .
    log: _log
    data: _data
    helpers: _helpers
extensions:
    enabled:
        - Codeception\Extension\PhpBuiltinServer
    config:
        Codeception\Extension\PhpBuiltinServer:
            hostname: localhost
            port: 8000
            documentRoot: ../web
            router: ../web/app.php
            directoryIndex: app.php
            startDelay: 1
            phpIni: /etc/php5/apache2/php.ini
```
