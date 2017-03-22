<?php

namespace Gibbo\Currency\Conversion\Calculator\ECB;

use Gibbo\Currency\Conversion\Currency;
use Gibbo\Currency\Conversion\Exchange;
use Gibbo\Currency\Conversion\ExchangeRate;
use Gibbo\Currency\Conversion\ExchangeRateCalculatorException;

/**
 * Calculates an exchange rate using data published by the European Central Bank.
 */
class ExchangeRateCalculator implements \Gibbo\Currency\Conversion\ExchangeRateCalculator
{

    private const URL = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    /**
     * @inheritdoc
     */
    public function rate(Exchange $exchange): ExchangeRate
    {
        try {
            $xml = new \SimpleXMLElement(static::URL, 0, true);
        } catch (\Exception $e) {
            throw new ExchangeRateCalculatorException('Could not fetch XML data from The European Central Bank');
        }

        $xml->registerXPathNamespace('ecb', 'http://www.ecb.int/vocabulary/2002-08-01/eurofxref');

        $baseRate    = $this->extractExchangeRateFromXml($xml, $exchange->getBase());
        $counterRate = $this->extractExchangeRateFromXml($xml, $exchange->getCounter());

        // All exchange rates provided by The European Central Bank are based on the euro so we need to calculate
        // the difference between the two to work out the rate.
        $rate = ($counterRate - $baseRate) + $counterRate;

        return new ExchangeRate($exchange, $rate);
    }

    /**
     * Extract the exchange rate from an XML document for a given currency.
     *
     * @param \SimpleXMLElement $xml      The xml to extract the exchange rate from.
     * @param Currency          $currency The currency to get the exchange rate for.
     *
     * @throws ExchangeRateCalculatorException Thrown when encountering an unsupported currency.
     *
     * @return float
     */
    private function extractExchangeRateFromXml(\SimpleXMLElement $xml, Currency $currency): float
    {
        $rate = $xml->xpath("//ecb:Cube[@currency='{$currency}']");

        if (!isset($rate[0]) || !($rate[0] instanceof \SimpleXMLElement)) {
            throw ExchangeRateCalculatorException::unsupportedCurrency($currency, $this);
        }

        $attributes = $rate[0]->attributes();

        if (!isset($attributes['rate']) || !($attributes['rate'] instanceof \SimpleXMLElement)) {
            throw new ExchangeRateCalculatorException("Could not extract rate from XML for currency '%s'", $currency);
        }

        return (float)(string)$attributes['rate'];
    }
}
