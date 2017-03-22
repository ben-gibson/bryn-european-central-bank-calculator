# Bryn - European Central Bank Calculator

[![Software License][ico-license]](LICENSE.md)

An exchange rate calculator for [Bryn](https://github.com/ben-gibson/bryn) that pulls data from the European Central Bank.

## Install

Use composer to install this library.

``` bash
$ composer require ben-gibson/bryn-european-central-bank-calculator
```

## Usage

``` php
<?php

require 'vendor/autoload.php';

$calculator = new \Gibbo\Bryn\Calculator\ECB\ExchangeRateCalculator();

$exchangeRate = $calculator->getRate(
    new \Gibbo\Bryn\Exchange(
        \Gibbo\Bryn\Currency::GBP(),
        \Gibbo\Bryn\Currency::USD()
    )
);

echo $exchangeRate;
echo PHP_EOL;
echo $exchangeRate->convert(550);
echo PHP_EOL;
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email ben.gibson.2011@gmail.com instead of using the issue tracker.

## Credits

- [Ben Gibson][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-license]: https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square
[link-author]: https://github.com/ben-gibson
[link-contributors]: ../../contributors