PhpBuiltinServer
================

Codeception extension for starting and stopping php built-in server

[![Build Status](https://secure.travis-ci.org/tiger-seo/PhpBuiltinServer.png?branch=master)](http://travis-ci.org/tiger-seo/PhpBuiltinServer)

## Minimum requirements

* Codeception 1.6.4
* PHP 5.4

## Installation

1. Install [Codeception](http://codeception.com) via Composer
2. Add `codeception/phpbuiltinserver: "*"` to your `composer.json`
3. Run `composer install`
4. Include extensions into `codeception.yml` configuration:

Sample:

``` yaml
paths:
    tests: tests
    log: tests/_log
    data: tests/_data
    helpers: tests/_helpers
extensions:
    enabled:
      - Codeception\Extension\PhpBuiltinServer
    config:
      Codeception\Extension\PhpBuiltinServer:
          hostname: localhost
          port: 8000
          documentRoot: tests/_data
```
