<?php

namespace App\Traits;

use App\Services\OddspediaService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

trait WebScrapingTrait
{
    private function getUrlContents(string $url, array $query = [], array $headers = []): string
    {
        $httpClient = new Client([
            RequestOptions::VERIFY => false,
            RequestOptions::TIMEOUT => 60,
            'base_uri' => OddspediaService::$baseUrl
        ]);

        $defaultHeaders = [
            'authority' => str_replace('https://', '', OddspediaService::$baseUrl),
            'accept' => 'application/json, text/plain, */*',
            'accept-language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            'baggage' => 'sentry-environment=production,sentry-release=1.213.1,sentry-public_key=5ee11cd5558a468388340fbac8cfe782,sentry-trace_id=804090d76bab47ab92f805566b4d38f9,sentry-sample_rate=0.01,sentry-sampled=false',
            'cookie' => 'accept-cookies=true; varnish_odds_format=decimal; varnish_wettsteuer_enabled=0; varnish_timezone=America/Sao_Paulo; first_visit=no; HAOSSID=5f03b6a1b44fa; AMCVS_9CE579FD5DCD8B590A495E09%40AdobeOrg=1; SSID=CQBL4x0AAAAAAADZa6Bl2CuAANlroGUFAAAAAAAAAAAArg6iZQATkw; SSSC=2.G7322971573864901592.5|0.0; __cf_bm=e37rqFKfYHT7Zjsrcek2VnZDD88SdbkrmDEyw8XwcnI-1705121614-1-AaRVuLhvsg2OePraZBvwd5GkFxqOIhby8A2Zj3RHTNNkc0vhVCJrRCjQBogQaqrxO5oxN3pwji4I06xtEUor9so=; SSRT=UheiZQQDAA; cf_clearance=6WuiDpXM0_izPXB_OozNZkWGPj_jrK15KO4yGj609NM-1705121621-1-AXdwU/oHOrX/Z8L33uaig2eRXdwhPMuL9g668mYQGMMRIceTtTz5AbyzWnojpXGrcTzWSnat2iWm2231Kf/uV30=; AMCV_9CE579FD5DCD8B590A495E09%40AdobeOrg=179643557%7CMCIDTS%7C19736%7CMCMID%7C59488103244111859718559463612291631516%7CMCOPTOUT-1705128821s%7CNONE%7CvVersion%7C5.5.0',
            'sec-ch-ua' => '"Chromium";v="118", "Google Chrome";v="118", "Not=A?Brand";v="99"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Linux"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'sentry-trace' => '804090d76bab47ab92f805566b4d38f9-9282db337dd45732-0',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36',
        ];

        $headers = array_merge($defaultHeaders, $headers);

        $response = $httpClient->request('get', $url, [
            RequestOptions::QUERY => $query,
            RequestOptions::HEADERS => $headers
        ]);

        $contents = $response->getBody()->getContents();

        return $contents;
    }
}
