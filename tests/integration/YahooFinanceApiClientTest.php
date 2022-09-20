<?php 

namespace App\Tests\Integration;
use App\Http\YahooFinanceApiClient;
use App\Tests\DatabaseDependantTestCase;

class YahooFinanceApiClientTest extends DatabaseDependantTestCase
{
    /**
     * @test
     * @group integration
     */
    public function testYahooFinanceApiClientReturnsCorrectData()
    {
        /** @var YahooFinanceApiClient */
        $yahooFinanceApiClient = self::$kernel->getContainer()->get('yahoo-finance-api-client');

        $symbol = "AMZN";
        $region = "US";

        // fetch stock profile for symbol and region
        $response = $yahooFinanceApiClient->fetchStockProfile($symbol, $region);

        // ensure that the client returns status code 200
        $this->assertEquals(200, $response['status']);

        // get stock profile from price object in content
        $stockProfile = json_decode($response['content'])->price;

        $price = $stockProfile->regularMarketPrice->raw;
        $previousClose = $stockProfile->regularMarketPreviousClose->raw;
        $priceChange = $price - $previousClose;

        $this->assertSame('AMZN', $stockProfile->symbol);
        $this->assertSame('NasdaqGS', $stockProfile->exchangeName);
        $this->assertSame('Amazon.com, Inc.', $stockProfile->shortName);
        $this->assertSame('US', $region);
        $this->assertSame('USD', $stockProfile->currency);

        $this->assertIsFloat($price);
        $this->assertIsFloat($previousClose);
        $this->assertIsFloat($priceChange);
    }
}