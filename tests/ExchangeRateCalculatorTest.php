<?php

namespace Gibbo\Bryn\Calculator\ECB\Test;

use PHPUnit\Framework\TestCase;

/**
 * Currency tests.
 */
class ExchangeRateCalculatorTest extends TestCase
{

    /**
     * Can the currency be initialised.
     *
     * @return void
     */
    public function testCanBeInitialised()
    {
        $this->assertInstanceOf(ExchangeRateCalculatorTest::class, new ExchangeRateCalculatorTest());
    }
}