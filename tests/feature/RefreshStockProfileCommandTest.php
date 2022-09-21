<?php

use App\Entity\Stock;
use App\Http\YahooFinanceApiClient;
use App\Tests\DatabaseDependantTestCase;
use Symfony\Component\Console\Application;
use App\Command\RefreshStockProfileCommand;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;


class RefreshStockProfileCommandTest extends DatabaseDependantTestCase
{
    public function testTheRefreshStockProfileCommandBehavesCorrectly()
    {
        // setup application for testing
        $application = new Application();

        $yahooFinanceApiClient = self::$kernel->getContainer()->get('yahoo-finance-api-client');
        
        // set serializer
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $application->add(new RefreshStockProfileCommand($this->entityManager, 
                                                        $yahooFinanceApiClient,
                                                        $serializer));

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