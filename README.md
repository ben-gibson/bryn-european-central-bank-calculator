# Bryn - European Central Bank Calculator

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status](https://travis-ci.org/ben-gibson/bryn-european-central-bank-calculator.svg?branch=master)](https://travis-ci.org/ben-gibson/bryn-european-central-bank-calculator)
[![Total Downloads][ico-downloads]][link-downloads]

An exchange rate calculator for [Bryn](https://github.com/ben-gibson/bryn) that pulls data from the European Central Bank. [HTTPlug](http://httplug.io/) 
is used to support multiple `HTTP` clients including `Guzzle`, `Buzz`, and `Curl`.

## Install

Use composer to install this library and your preferred `HTTP` client.

``` bash
$ composer require php-http/guzzle6-adapter
$ composer require ben-gibson/bryn-european-central-bank-calculator
```

## Usage

``` php
<?php
    
require 'vendor/autoload.php';
    
$calculator = \Gibbo\Bryn\Calculator\ECB\ECBCalculator::default();
    
$exchangeRate = $calculator->getRate(
    new \Gibbo\Bryn\Exchange(
        \Gibbo\Bryn\Currency::GBP(),
        \Gibbo\Bryn\Currency::USD()
    )
);
    
echo $exchangeRate;
echo $exchangeRate->convert(550);
echo $exchangeRate->flip()->convert(550);
    
/**
 * OUTPUTS:
 *
 * 1 GBP(£) = 1.25 USD($)
 * 686.2295
 * 440.814
 */
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Ben Gibson][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/ben-gibson/bryn-european-central-bank-calculator.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/ben-gibson/bryn-european-central-bank-calculator.svg?style=flat-square
[link-packagist]: https://packagist.org/packages/ben-gibson/bryn-european-central-bank-calculator
[link-downloads]: https://packagist.org/packages/ben-gibson/bryn-european-central-bank-calculator
[ico-license]: https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square
[link-author]: https://github.com/ben-gibson
[link-contributors]: ../../contributors
