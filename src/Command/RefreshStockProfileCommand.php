<?php

namespace App\Command;

use App\Entity\Stock;
use App\Http\YahooFinanceApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;


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

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;


    public function __construct(EntityManagerInterface $entityManager, 
                                YahooFinanceApiClient $yahooFinanceApiClient,
                                SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;

        $this->yahooFinanceApiClient = $yahooFinanceApiClient;

        $this->serializer = $serializer;

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
        /** @var JsonResponse */
        $stockProfileResponse = $this->yahooFinanceApiClient->fetchStockProfile(
            $input->getArgument('symbol'),
            $input->getArgument('region')
        );

        if($stockProfileResponse->getStatusCode() !== 200) {

            $output->writeln('error during response for symbol' . $input->getArgument('symbol') . '(' . $stockProfileResponse['status'] . ').');

            // return failure
            return Command::FAILURE;
        }

        // deserialize Json content to Stock
        $stock = $this->serializer->deserialize($stockProfileResponse->getContent(), Stock::class, 'json');

        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        $output->writeln($stock->getShortName() . ' has been saved / updated.');

        return Command::SUCCESS;
    }
}
