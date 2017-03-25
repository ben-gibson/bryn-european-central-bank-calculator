<?php

namespace Gibbo\Bryn\Calculator\ECB;

use Gibbo\Bryn\Currency;
use Gibbo\Bryn\Exchange;
use Gibbo\Bryn\ExchangeRate;
use Gibbo\Bryn\ExchangeRateCalculatorException;
use Http\Client\Common\HttpMethodsClient;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;

/**
 * Calculates an exchange rate using data published by the European Central Bank.
 */
class ExchangeRateCalculator implements \Gibbo\Bryn\ExchangeRateCalculator
{

    const URL = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * Constructor.
     *
     * @param HttpMethodsClient $httpClient
     */
    public function __construct(HttpMethodsClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }


    /**
     * A convenience method for initialising the default implementation.
     *
     * @return static
     */
    public static function default()
    {
        return new static(new HttpMethodsClient(HttpClientDiscovery::find(), MessageFactoryDiscovery::find()));
    }

    /**
     * @inheritdoc
     */
    public function getRate(Exchange $exchange): ExchangeRate
    {
        $response = $this->httpClient->send('GET', static::URL);

        if ($response->getStatusCode() !== 200) {
            throw new ExchangeRateCalculatorException(sprintf(
                "Unsuccessful response status '%d' returned from The European Central Bank",
                $response->getStatusCode()
            ));
        }

        try {
            $xml = new \SimpleXMLElement($response->getBody()->getContents());
        } catch (\Exception $e) {
            throw new ExchangeRateCalculatorException('Invalid XML received from The European Central Bank');
        }

        $xml->registerXPathNamespace('ecb', 'http://www.ecb.int/vocabulary/2002-08-01/eurofxref');

        // The exchange rates provided by The European Central Bank are euro based so we can return
        // the exchange rate as given if the base currency of the exchange is also euro
        if ($exchange->getBase()->isEuro()) {
            return new ExchangeRate($exchange, $this->extractExchangeRateFromXml($xml, $exchange->getCounter()));
        }

        // If the counter currency is the euro we can take the exchange rate as given and flip it.
        if ($exchange->getCounter()->isEuro()) {
            $exchangeRate = new ExchangeRate($exchange->flip(), $this->extractExchangeRateFromXml($xml, $exchange->getBase()));

            return $exchangeRate->flip();
        }

        // If the exchange doesn't involve euros we need to calculate the different between the two currencies euro
        // exchange rate to get the exchange rate between them.
        $baseRate    = $this->extractExchangeRateFromXml($xml, $exchange->getBase());
        $counterRate = $this->extractExchangeRateFromXml($xml, $exchange->getCounter());

        return new ExchangeRate($exchange, $counterRate / $baseRate);
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
            throw new ExchangeRateCalculatorException("Could not extract exchange rate from XML for currency '%s'", $currency);
        }

        return (float)(string)$attributes['rate'];
    }
}
