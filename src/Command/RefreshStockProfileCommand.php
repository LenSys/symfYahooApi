<?php

namespace App\Command;

use App\Entity\Stock;
use App\Http\YahooFinanceApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:refresh-stock-profile',
    description: 'Retrieve a stock profile from the Yahoo Finance API.',
)]

class RefreshStockProfileCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;


    /**
     * @var YahooFinanceApiClient
     */
    private YahooFinanceApiClient $yahooFinanceApiClient;


    public function __construct(EntityManagerInterface $entityManager, YahooFinanceApiClient $yahooFinanceApiClient)
    {
        $this->entityManager = $entityManager;

        $this->yahooFinanceApiClient = $yahooFinanceApiClient;

        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->addArgument('symbol', InputArgument::REQUIRED, 'Stock symbol, e.g. AMZN')
            ->addArgument('region', InputArgument::REQUIRED, 'Region of the company')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $stockProfile = $this->yahooFinanceApiClient->fetchStockProfile(
            $input->getArgument('symbol'),
            $input->getArgument('region')
        );

        $price = $stockProfile->price;
        $previousClose = $stockProfile->previousClose;
        $priceChange = $price - $previousClose;

        $stock = new Stock();
        $stock->setCurrency($stockProfile->currency);
        $stock->setExchangeName($stockProfile->exchangeName);
        $stock->setSymbol($stockProfile->symbol);
        $stock->setShortName($stockProfile->shortName);
        $stock->setRegion($stockProfile->region);
        $stock->setPreviousClose($previousClose);
        $stock->setPrice($price);
        $stock->setPriceChange($priceChange);

        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
