# Semver

[![Build Status](https://travis-ci.org/omines/semver.svg?branch=master)](https://travis-ci.org/omines/semver)
[![Coverage Status](https://coveralls.io/repos/omines/semver/badge.svg?branch=master&service=github)](https://coveralls.io/github/omines/semver?branch=master)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/omines/semver.svg)](https://scrutinizer-ci.com/g/omines/semver/?branch=master)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/6bf49b9f-c9fd-456f-962e-6238e9f5e61e.svg)](https://insight.sensiolabs.com/projects/6bf49b9f-c9fd-456f-962e-6238e9f5e61e)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/omines/semver/master/LICENSE)

Semantic Versioning implementation for PHP including constraints, filters, sorting and iterable map. Conforms to Semver
specification [2.0.0](http://semver.org/spec/v2.0.0.html) but also support loose parsing of non-compliant but similar versioning systems.

## To-do

This library is still being developed and not remotely feature complete.

[] Port all unit tests from node's implementation to ensure identical operation
[] Implement ranges
[] Add stability support
[] Add lossy parser for non-Semver compliant version strings
[] Add filters for stability and ranges to iterators
[] Add extra collection functionality for trimming by filters

## Contributions

If you run into problems using this library, or would like to request additional features, please open an issue.

Pull requests are only considered if they follow [PSR-2](http://www.php-fig.org/psr/psr-2/) coding standards and include
full unit tests maintaining coverage on files and lines.
