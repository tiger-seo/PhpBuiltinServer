PhpBuiltinServer [![Latest Stable](https://poser.pugx.org/codeception/phpbuiltinserver/version.png)](https://packagist.org/packages/codeception/phpbuiltinserver) [![Total Downloads](https://poser.pugx.org/codeception/phpbuiltinserver/downloads.png)](https://packagist.org/packages/codeception/phpbuiltinserver)
================

Codeception extension to start and stop PHP built-in web server for your tests.

| Codeception Branch | PhpBuiltinServer Branch | Status |
| ------- | -------- | -------- |
| **Codeception 1.x** | **1.1.x** | [![Build Status](https://secure.travis-ci.org/tiger-seo/PhpBuiltinServer.png?branch=v1.1.x)](http://travis-ci.org/tiger-seo/PhpBuiltinServer) |
| **Codeception 2.0** | **1.2.x** | [![Build Status](https://secure.travis-ci.org/tiger-seo/PhpBuiltinServer.png?branch=v1.2.x)](http://travis-ci.org/tiger-seo/PhpBuiltinServer) |
| **Codeception 2.1, 2.2** | **1.3.x** | [![Build Status](https://secure.travis-ci.org/tiger-seo/PhpBuiltinServer.png?branch=v1.3.x)](http://travis-ci.org/tiger-seo/PhpBuiltinServer) |
| **Codeception 2.3** | **1.4.x** | [![Build Status](https://secure.travis-ci.org/tiger-seo/PhpBuiltinServer.png?branch=v1.4.x)](http://travis-ci.org/tiger-seo/PhpBuiltinServer) |
| **Codeception 3.0** | **1.5.x** | [![Build Status](https://secure.travis-ci.org/tiger-seo/PhpBuiltinServer.png?branch=v1.5.x)](http://travis-ci.org/tiger-seo/PhpBuiltinServer) |
| **Codeception 4.0** | **master** | [![Build Status](https://secure.travis-ci.org/tiger-seo/PhpBuiltinServer.png?branch=master)](http://travis-ci.org/tiger-seo/PhpBuiltinServer) |

## Minimum requirements

* Codeception 4.x
* PHP >= 7.2
* PHPUnit >= 6.0

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
            autostart: true
            documentRoot: tests/_data
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
            autostart: true
            documentRoot: ../web
            router: ../web/app.php
            directoryIndex: app.php
            startDelay: 1
            phpIni: /etc/php5/apache2/php.ini
```
