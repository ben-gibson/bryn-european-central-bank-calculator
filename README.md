# Currency Conversion

[![Software License][ico-license]](LICENSE.md)

An exchange rate calculator for the `PHP` library [currency-conversion](ben-gibson/currency-conversion) that uses data from the European Central Bank.

## Install

Use composer to install this library.

``` bash
$ composer require ben-gibson/currency-conversion-calculator-european-central-bank
```

## Usage

``` php
<?php

require 'vendor/autoload.php';

$calculator = new \Gibbo\Currency\Conversion\Calculator\ECB\ExchangeRateCalculator();

$exchangeRate = $calculator->rate(
    new \Gibbo\Currency\Conversion\Exchange(
        \Gibbo\Currency\Conversion\Currency::GBP(),
        \Gibbo\Currency\Conversion\Currency::USD()
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