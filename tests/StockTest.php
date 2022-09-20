<?php 

namespace App\Tests;

use App\Entity\Stock;
use App\Tests\DatabaseDependantTestCase;

class StockTest extends DatabaseDependantTestCase 
{
    public function test_init_unit_tests()
    {
        $this->assertTrue(true);
    }

    public function test_ensure_test_environment()
    {
        $this->assertSame('test', self::$kernel->getEnvironment());
    }

    public function testStockRecordCanBeCreatedInTheDatabase()
    {
        $price = 1000;
        $previousClose = 1100;
        $priceChange = $price - $previousClose;

        $stock = new Stock();
        $stock->setSymbol('AMZN');
        $stock->setShortName('Amazon Inc.');
        $stock->setCurrency('USD');
        $stock->setExchangeName('Nasdaq');
        $stock->setRegion('US');
        $stock->setPrice($price);
        $stock->setPreviousClose($previousClose);
        $stock->setPriceChange($priceChange);

        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        $stockRepository = $this->entityManager->getRepository(Stock::class);
        $stockEntry = $stockRepository->findOneBy(['symbol' => 'AMZN']);

        $this->assertEquals('Amazon Inc.', $stockEntry->getShortName());
    }
}