<?php

namespace Gibbo\Bryn\Calculator\ECB\Test;

use Gibbo\Bryn\Calculator\ECB\ECBCalculator;
use Gibbo\Bryn\Currency;
use Gibbo\Bryn\Exchange;
use Gibbo\Bryn\ExchangeRate;
use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Calculator tests.
 */
class ECBCalculatorTest extends TestCase
{

    /**
     * Can the currency be initialised.
     *
     * @return void
     */
    public function testCanBeInitialised()
    {
        $this->assertInstanceOf(ECBCalculator::class, $this->getCalculator());
    }

    /**
     * Test an exception is thrown when an unsuccessful response is received.
     *
     * @expectedException \Gibbo\Bryn\ExchangeRateCalculatorException
     * @expectedExceptionMessage Unsuccessful response status '500' returned from The European Central Bank
     *
     * @return void
     */
    public function testDoesThrowOnUnsuccessfulResponse()
    {
        $httpClient = new Client();

        $httpClient->addResponse($this->getMockResponse('', 500));

        $this->getCalculator($httpClient)->getRate(new Exchange(Currency::GBP(), Currency::USD()));
    }

    /**
     * Test an exception is thrown when invalid xml is provided in the response.
     *
     * @expectedException \Gibbo\Bryn\ExchangeRateCalculatorException
     * @expectedExceptionMessage Invalid XML received from The European Central Bank
     *
     * @return void
     */
    public function testDoesThrowOnInvalidXml()
    {
        $httpClient = new Client();

        $httpClient->addResponse($this->getMockResponse('foo'));

        $this->getCalculator($httpClient)->getRate(new Exchange(Currency::GBP(), Currency::USD()));
    }

    /**
     * Test an exception is thrown when an unsupported currency is given.
     *
     * @expectedException \Gibbo\Bryn\ExchangeRateCalculatorException
     * @expectedExceptionMessage The currency 'USD' is not supported by the calculator (Gibbo\Bryn\Calculator\ECB\ECBCalculator)
     *
     * @return void
     */
    public function testDoesThrowOnUnsupportedCurrency()
    {
        $httpClient = new Client();

        $contents = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<gesmes:Envelope xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01" xmlns="http://www.ecb.int/vocabulary/2002-08-01/eurofxref">
	<gesmes:subject>Reference rates</gesmes:subject>
	<gesmes:Sender>
		<gesmes:name>European Central Bank</gesmes:name>
	</gesmes:Sender>
	<Cube>
		<Cube time='2017-03-24'>
			<Cube currency='JPY' rate='120.09'/>
			<Cube currency='BGN' rate='1.9558'/>
			<Cube currency='CZK' rate='27.021'/>
			<Cube currency='DKK' rate='7.4378'/>
			<Cube currency='GBP' rate='0.86600'/>
		</Cube>
	</Cube>
</gesmes:Envelope>
XML;

        $httpClient->addResponse($this->getMockResponse($contents));

        $this->getCalculator($httpClient)->getRate(new Exchange(Currency::GBP(), Currency::USD()));
    }

    /**
     * Test the exchange rate can be calculated.
     *
     * @param Exchange     $exchange
     * @param ExchangeRate $expectedRate
     *
     * @dataProvider exchangeRateProvider
     *
     * @return void
     */
    public function testDoesCalculateExchangeRate(Exchange $exchange, ExchangeRate $expectedRate)
    {
        $httpClient = new Client();

        $contents = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<gesmes:Envelope xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01" xmlns="http://www.ecb.int/vocabulary/2002-08-01/eurofxref">
	<gesmes:subject>Reference rates</gesmes:subject>
	<gesmes:Sender>
		<gesmes:name>European Central Bank</gesmes:name>
	</gesmes:Sender>
	<Cube>
		<Cube time='2017-03-24'>
		    <Cube currency="USD" rate="1.0805"/>
			<Cube currency='JPY' rate='120.09'/>
			<Cube currency='BGN' rate='1.9558'/>
			<Cube currency='CZK' rate='27.021'/>
			<Cube currency='DKK' rate='7.4378'/>
			<Cube currency='GBP' rate='0.86600'/>
			<Cube currency="HUF" rate="309.81"/>
            <Cube currency="PLN" rate="4.2695"/>
            <Cube currency="RON" rate="4.5527"/>
            <Cube currency="SEK" rate="9.5373"/>
            <Cube currency="CHF" rate="1.0718"/>
            <Cube currency="NOK" rate="9.1793"/>
            <Cube currency="HRK" rate="7.4198"/>
            <Cube currency="RUB" rate="61.6859"/>
            <Cube currency="TRY" rate="3.9176"/>
            <Cube currency="AUD" rate="1.4182"/>
            <Cube currency="BRL" rate="3.3845"/>
            <Cube currency="CAD" rate="1.4448"/>
            <Cube currency="CNY" rate="7.4406"/>
            <Cube currency="HKD" rate="8.3933"/>
            <Cube currency="IDR" rate="14399.28"/>
            <Cube currency="ILS" rate="3.9347"/>
            <Cube currency="INR" rate="70.6520"/>
            <Cube currency="KRW" rate="1210.37"/>
            <Cube currency="MXN" rate="20.4282"/>
            <Cube currency="MYR" rate="4.7800"/>
            <Cube currency="NZD" rate="1.5401"/>
            <Cube currency="PHP" rate="54.254"/>
            <Cube currency="SGD" rate="1.5126"/>
            <Cube currency="THB" rate="37.353"/>
            <Cube currency="ZAR" rate="13.4816"/>
		</Cube>
	</Cube>
</gesmes:Envelope>
XML;

        $httpClient->addResponse($this->getMockResponse($contents));

        $calculator = $this->getCalculator($httpClient);

        $this->assertEquals($expectedRate, $calculator->getRate($exchange));
    }

    /**
     * Provides exchange rates.
     *
     * @return array
     */
    public function exchangeRateProvider(): array
    {
        return [
            'GBP to USD' => [
                new Exchange(Currency::GBP(), Currency::USD()),
                new ExchangeRate(new Exchange(Currency::GBP(), Currency::USD()), 1.24769)
            ],
            'USD to GBP' => [
                new Exchange(Currency::USD(), Currency::GBP()),
                new ExchangeRate(new Exchange(Currency::USD(), Currency::GBP()), 0.80148)
            ],
            'GBP to Euro' =>  [
                new Exchange(Currency::Euro(), Currency::GBP()),
                new ExchangeRate(new Exchange(Currency::Euro(), Currency::GBP()), 0.86600)
            ],
            'Euro to GBP' =>  [
                new Exchange(Currency::GBP(), Currency::Euro()),
                new ExchangeRate(new Exchange(Currency::GBP(), Currency::Euro()), 1.15473)
            ],
        ];
    }

    /**
     * Get the calculator under test.
     *
     * @param Client|null $httpClient
     *
     * @return ECBCalculator
     */
    private function getCalculator(Client $httpClient = null): ECBCalculator
    {
        return new ECBCalculator(
            new HttpMethodsClient($httpClient ?: new Client(), MessageFactoryDiscovery::find())
        );
    }

    /**
     * Get a mock response.
     *
     * @param string $contents The response contents
     * @param int $status      The response status
     *
     * @return ResponseInterface
     */
    private function getMockResponse($contents, $status = 200): ResponseInterface
    {
        $body     = $this->createMock(StreamInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $response
            ->method('getBody')
            ->willReturn($body);

        $body
            ->method('getContents')
            ->willReturn($contents);

        $response
            ->method('getStatusCode')
            ->willReturn($status);

        return $response;
    }
}
