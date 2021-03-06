# Semver

[![Build Status](https://travis-ci.org/omines/semver.svg?branch=master)](https://travis-ci.org/omines/semver)
[![Scrutinizer Coverage](https://img.shields.io/scrutinizer/coverage/g/omines/semver.svg)](https://scrutinizer-ci.com/g/omines/semver/?branch=master)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/omines/semver.svg)](https://scrutinizer-ci.com/g/omines/semver/?branch=master)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/6bf49b9f-c9fd-456f-962e-6238e9f5e61e.svg)](https://insight.sensiolabs.com/projects/6bf49b9f-c9fd-456f-962e-6238e9f5e61e)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/omines/semver/master/LICENSE)

Semantic Versioning implementation for PHP including constraints, filters, sorting and iterable map. Conforms to Semver
specification [2.0.0](http://semver.org/spec/v2.0.0.html) but also support loose parsing of non-compliant but similar versioning systems.

All core features like the parsing and processing of versions and expressions are implemented and 100% unit tested, but the
library is not yet feature complete. Active development is currently on hold. Visit the [open issues](https://github.com/omines/semver/issues)
to see what's on the list before going 1.0.0.

## Installation

[![Packagist](https://img.shields.io/packagist/v/omines/semver.svg)](https://packagist.org/packages/omines/semver)
[![Packagist](https://img.shields.io/packagist/vpre/omines/semver.svg)](https://packagist.org/packages/omines/semver#dev-master)

The recommended way to install this library is through [Composer](http://getcomposer.org):
```bash
composer require omines/semver
```

If you're not familiar with `composer` follow the installation instructions for
[Linux/Unix/Mac](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx) or
[Windows](https://getcomposer.org/doc/00-intro.md#installation-windows), and then read the
[basic usage introduction](https://getcomposer.org/doc/01-basic-usage.md).

## Usage

Code says more than a thousand words:
```php
// Versions
$first = new Version('0.1.2-alpha.1-build.684');
$second = Version::fromString('1.2.3');
$third = new Version('2.4.6');

// Comparison
$first->greaterThan($second);               // false
$first->greaterThanOrEqual($second);        // false
$first->lessThan($second);                  // true
$first->lessThanOrEqual($second);           // true
$first->equals($second);                    // false
$first->compare($second);                   // negative
$second->compare($first);                   // positive
$first->compare($first);                    // 0

// Utility (can use either varargs or an array)
Version::highest($first, $second, $third);  // $third
Version::lowest([$first, $second, $third]); // $first

// Expressions
$expression = Expression::fromString('>=2.3 || ^1.2');
$expression->matches($first);               // false
$second->matches($expression);              // true

// List
$list = new VersionList([$third, $second]);
$list[] = $second;
$list[1] = $first;
$list->sort();
$list[1] == $second;                        // true
foreach ($list as $version) { ... }         // $version instanceof Version
$list->each($closure);                      // calls closure with every element

// Map
$map = new VersionMap([
    $first => 'Package 1'
]);
$map[$second] = 'Package 2';
foreach ($map as $key => $value) { ... }    // $key instanceof Version
$map->each($closure);                       // calls closure with every (&$value, $key)
```
If errors occur a `SemverException` is thrown.

## Contributions

If you run into problems using this library, or would like to request additional features, please open an issue.

Pull requests are only considered if they follow [PSR-2](http://www.php-fig.org/psr/psr-2/) coding standards and include
full unit tests maintaining coverage on files and lines.

## Legal

This software was developed for internal use at [Omines Full Service Internetbureau](https://www.omines.nl/)
in Eindhoven, the Netherlands. It is shared with the general public under the permissive MIT license, without
any guarantee of fitness for any particular purpose. Refer to the included `LICENSE` file for more details.
