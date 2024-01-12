<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

trait WebScrapingTrait
{
    private function getUrlContents(string $url): string
    {
        $httpClient = new Client([
            RequestOptions::VERIFY => false,
            RequestOptions::TIMEOUT => 60,
            'base_uri' => 'https://oddspedia.com'
        ]);

        $response = $httpClient->request('get', $url);

        $contents = $response->getBody()->getContents();

        return $contents;
    }
}
