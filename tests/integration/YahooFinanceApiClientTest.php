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
        /** @var JsonResponse */
        $response = $yahooFinanceApiClient->fetchStockProfile($symbol, $region);

        // ensure that the client returns status code 200
        $this->assertEquals(200, $response->getStatusCode());

        // get stock profile from price object in content
        $stockProfile = json_decode($response->getContent());

        $this->assertSame('AMZN', $stockProfile->symbol);
        $this->assertSame('Amazon.com, Inc.', $stockProfile->shortName);
        $this->assertSame('US', $stockProfile->region);
        $this->assertSame('NasdaqGS', $stockProfile->exchangeName);
        $this->assertSame('USD', $stockProfile->currency);

        $this->assertIsFloat($stockProfile->price);
        $this->assertIsFloat($stockProfile->previousClose);
        $this->assertIsFloat($stockProfile->priceChange);
    }
}