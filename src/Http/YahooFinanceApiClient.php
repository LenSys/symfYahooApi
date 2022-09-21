<?php

namespace App\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class YahooFinanceApiClient
{    

    /** @var HttpClientInterface */
    private HttpClientInterface $httpClient;


    private const URL = "https://yh-finance.p.rapidapi.com/stock/v2/get-profile";

    private const X_RAPID_API_HOST = "yh-finance.p.rapidapi.com";

    private $rapidApiKey;


    public function __construct(HttpClientInterface $httpClient, $rapidApiKey)
    {
        $this->httpClient = $httpClient;

        $this->rapidApiKey = $rapidApiKey;
    }


    /**
     * Fetch the stock profile from the Yahoo Finance API.
     *
     * @param  mixed $symbol The stock symbol
     * @param  mixed $region The region of the stock
     * @return void
     */
    public function fetchStockProfile(string $symbol, string $region): JsonResponse
    {
        /*
        $stockProfile = [
            'status' => 200,
            'content' => json_encode([
                'symbol' => 'AMZN',
                'shortName' => 'Amazon.com, Inc.',
                'exchangeName' => 'NasdaqGS',
                'region' => 'US',
                'currency' => 'USD',
                'price' => 1000.10,
                'previousClose' => 995.54,
                'priceChange' => 4.56
            ])
        ];
        */

        
        /** @var ResponseInterface */
        /*
        // use this code part to get data from RapidAPI 
        $response = $this->httpClient->request('GET', self::URL, [
            'query' => [
                'symbol' => 'AMZN',
                'region' => 'US'
            ],
            'headers' => [
                'X-RapidAPI-Host' => self::X_RAPID_API_HOST,
                'X-RapidAPI-Key' => $this->rapidApiKey
            ]
        ]);

        $responseStatusCode = $response->getStatusCode();
        $responseContent = $response->getContent();

        file_put_contents("public/response.txt", $response->getContent());
        */

        $responseStatusCode = 200;
        $responseContent = file_get_contents("public/response.txt");

        // handle non 200 response
        if ($responseStatusCode !== 200) {

            return new JsonResponse('Finance API Client Error ', 400);
        }

        // get stock profile from price object in content
        $stockProfile = json_decode($responseContent)->price;

        $price = $stockProfile->regularMarketPrice->raw;
        $previousClose = $stockProfile->regularMarketPreviousClose->raw;
        $priceChange = $price - $previousClose;


        $stockProfileAsArray = [
            'symbol' => $stockProfile->symbol,
            'shortName' => $stockProfile->shortName,
            'region' => $region,
            'exchangeName' => $stockProfile->exchangeName,
            'currency' => $stockProfile->currency,
            'price' => $price,
            'previousClose' => $previousClose,
            'priceChange' => $priceChange,
        ];

        /*
        return [
            'status' => $responseStatusCode,
            'content' => $stockProfileAsArray
        ];*/
        return new JsonResponse($stockProfileAsArray, $responseStatusCode);
    }
}