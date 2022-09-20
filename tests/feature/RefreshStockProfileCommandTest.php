<?php

use App\Entity\Stock;
use App\Tests\DatabaseDependantTestCase;
use Symfony\Component\Console\Application;
use App\Command\RefreshStockProfileCommand;
use Symfony\Component\Console\Tester\CommandTester;
use App\Http\YahooFinanceApiClient;


class RefreshStockProfileCommandTest extends DatabaseDependantTestCase
{
    public function testTheRefreshStockProfileCommandBehavesCorrectly()
    {
        // setup application for testing
        $application = new Application();
        // $yahooFinanceApiClient = new YahooFinanceApiClient();
        $yahooFinanceApiClient = self::$kernel->getContainer()->get('yahoo-finance-api-client');
        $application->add(new RefreshStockProfileCommand($this->entityManager, $yahooFinanceApiClient));

        // command
        $command = $application->find('app:refresh-stock-profile');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        $stockRepository = $this->entityManager->getRepository(Stock::class);

        /** @var Stock stock */
        $stock = $stockRepository->findOneBy(['symbol' => 'AMZN']);

        $this->assertSame('USD', $stock->getCurrency());
        $this->assertSame('NasdaqGS', $stock->getExchangeName());
        $this->assertSame('AMZN', $stock->getSymbol());
        $this->assertSame('Amazon.com, Inc.', $stock->getShortName());
        $this->assertSame('US', $stock->getRegion());
        $this->assertGreaterThan(50, $stock->getPreviousClose());
        $this->assertGreaterThan(50, $stock->getPrice());
    }
}