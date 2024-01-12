<?php

namespace App\Jobs;

use App\Models\Game;
use App\Traits\WebScrapingTrait;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GetDailyMatchesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use WebScrapingTrait;

    public function __construct(
        private string $sport,
        private string $date,
        private bool $popularLeagueOnly = true
    )
    {
        //
    }

    public function handle(): void
    {
        $matches = [];

        $page = 1;

        while (true) {

            $this->log('debug', 'Obtendo partidas da página ' . $page . '...');

            $pageMatches = $this->getMatchList($page, $this->popularLeagueOnly);

            //$this->log('debug', 'Partidas obtidas: ' . count($pageMatches) . '.');

            if (empty($pageMatches)) {
                break;
            }

            $matches = array_merge($matches, $pageMatches);

            $page++;
        }

        foreach ($matches as $row) {

            $game = $this->getGame($row);

            #$this->log('debug', 'Iniciando captura de odds', $game->toArray());

            UpdateGameOddsJob::dispatch($game)
                ->onQueue('odds');
        }
    }

    private function getMatchPoll(): array
    {
        $query = [
            'geoCode' => 'BR',
            'bookmakerGeoState' => '',
            'bookmakerGeoCode' => 'BR',
            'sport' => $this->sport,
            'date' => $this->date . 'T03:00:00Z',
            'language' => 'en'
        ];

        $headers = [
            'authority' => 'oddspedia.com',
            'accept' => 'application/json, text/plain, */*',
            'accept-language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            'baggage' => 'sentry-environment=production,sentry-release=1.213.1,sentry-public_key=5ee11cd5558a468388340fbac8cfe782,sentry-trace_id=c530b39772d145febe7da2631d273192',
            'cookie' => 'accept-cookies=true; varnish_odds_format=decimal; varnish_wettsteuer_enabled=0; varnish_timezone=America/Sao_Paulo; first_visit=no; SSID=CQAKUB0AAAAAAADZa6Bl2CuAANlroGUDAAAAAAAAAAAAChahZQATkw; SSSC=2.G7322971573864901592.3|0.0; SSRT=ChahZQQDAA; HAOSSID=270269b563252; __cf_bm=3.ZcztAa03aLFlAXEgZM69WQpU03yLvhG9V0dovo8W4-1705055756-1-Ad6MZ2VsOsj6OkoHDBkqxsfDl3vMQ3OdTVFFGyzbnrozpCLwskE31GkHVZJ557Rqg8X+ojaJGMQ0OIqxEVuf0rU=; cf_clearance=Of2SCBy8idU6IPcl3gfeyqmgzTuCqestXd1V8OSwZkM-1705055757-0-2-81bc6d8f.6688edd7.628e5665-0.2.1705055757; AMCVS_9CE579FD5DCD8B590A495E09%40AdobeOrg=1; AMCV_9CE579FD5DCD8B590A495E09%40AdobeOrg=179643557%7CMCIDTS%7C19734%7CMCMID%7C59488103244111859718559463612291631516%7CMCOPTOUT-1705062991s%7CNONE%7CvVersion%7C5.5.0',
            'referer' => 'https://oddspedia.com/' . $this->sport,
            'sec-ch-ua' => '"Chromium";v="118", "Google Chrome";v="118", "Not=A?Brand";v="99"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Linux"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'sentry-trace' => 'c530b39772d145febe7da2631d273192-a99a768051e78ea7-0',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36',
        ];

        $url = '/api/v1/getMatchPoll';

        $contents = $this->getUrlContents($url, $query, $headers);

        $result = json_decode($contents, true);

        return $result['data'];
    }

    private function getMatchList(int $page = 1, bool $popularLeagueOnly = true): array
    {
        $date = $this->date;

        $afterDate = date('Y-m-d', strtotime($date . ' +1 day'));

        $query = [
            'excludeSpecialStatus' => '0',
            'sortBy' => 'default',
            'perPageDefault' => '50',
            'startDate' => $date . 'T03:00:00Z',
            'endDate' => $afterDate . 'T02:59:59Z',
            'geoCode' => 'BR',
            'status' => 'all',
            'sport' => $this->sport,
            'popularLeaguesOnly' => $popularLeagueOnly ? '1' : '0',
            'page' => $page,
            'perPage' => '50',
            'language' => 'en',
        ];

        $headers = [
            'authority' => 'oddspedia.com',
            'accept' => 'application/json, text/plain, */*',
            'accept-language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            'baggage' => 'sentry-environment=production,sentry-release=1.213.1,sentry-public_key=5ee11cd5558a468388340fbac8cfe782,sentry-trace_id=c530b39772d145febe7da2631d273192',
            'cookie' => 'accept-cookies=true; varnish_odds_format=decimal; varnish_wettsteuer_enabled=0; varnish_timezone=America/Sao_Paulo; first_visit=no; SSID=CQAKUB0AAAAAAADZa6Bl2CuAANlroGUDAAAAAAAAAAAAChahZQATkw; SSSC=2.G7322971573864901592.3|0.0; SSRT=ChahZQQDAA; HAOSSID=270269b563252; __cf_bm=3.ZcztAa03aLFlAXEgZM69WQpU03yLvhG9V0dovo8W4-1705055756-1-Ad6MZ2VsOsj6OkoHDBkqxsfDl3vMQ3OdTVFFGyzbnrozpCLwskE31GkHVZJ557Rqg8X+ojaJGMQ0OIqxEVuf0rU=; cf_clearance=Of2SCBy8idU6IPcl3gfeyqmgzTuCqestXd1V8OSwZkM-1705055757-0-2-81bc6d8f.6688edd7.628e5665-0.2.1705055757; AMCVS_9CE579FD5DCD8B590A495E09%40AdobeOrg=1; AMCV_9CE579FD5DCD8B590A495E09%40AdobeOrg=179643557%7CMCIDTS%7C19734%7CMCMID%7C59488103244111859718559463612291631516%7CMCOPTOUT-1705062991s%7CNONE%7CvVersion%7C5.5.0',
            'referer' => 'https://oddspedia.com/' . $this->sport,
            'sec-ch-ua' => '"Chromium";v="118", "Google Chrome";v="118", "Not=A?Brand";v="99"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Linux"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'sentry-trace' => 'c530b39772d145febe7da2631d273192-a99a768051e78ea7-0',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36',
        ];

        $url = '/api/v1/getMatchList';

        try {

            $contents = $this->getUrlContents($url, $query, $headers);

        } catch (ClientException $e) {
                
            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

            if ($statusCode === Response::HTTP_NOT_FOUND) {
                return [];
            }

            throw $e;
        }

        $result = json_decode($contents, true);

        $pageMatches = $this->getMatchesFromPage($result['data']);

        return $pageMatches;
    }

    private function getGame(array $data): Game
    {
        $game = Game::updateOrCreate(
            [
                'oddspedia_id' => $data['oddspedia_id']
            ],
            [
                'home' => $data['ht'],
                'away' => $data['at'],
                'url' => $data['url'],
                'start_at' => $data['start_at'],
                'sport' => $data['sport'],
                'category' => $data['category'],
                'league' => $data['league'],
            ]
        );
        
        return $game;
    }

    private function getMatchesFromPage(array $page): array
    {
        $sportList = $page['sportList'];

        $matchList = $page['matchList'];

        $categoryList = $page['categoryList'];

        $leagueList = $page['leagueList'];

        $matches = [];

        foreach ($matchList as $row) {

            $match = [
                'oddspedia_id' => $row['id'],
                'ht' => $row['ht'],
                'at' => $row['at'],
                'url' => $row['uri'],
                'start_at' => $row['md'],
                'sport' => $sportList[$row['sport_id']]['slug'],
                'category' => $categoryList[$row['category_id']]['slug'],
                'league' => $leagueList[$row['league_id']]['slug'],
            ];

            $matches[] = $match;
        }

        return $matches;
    }

    private function log(string $channel, string $message, array $context = []): void
    {
        $preffixParts = [
            'matches',
            $this->sport,
            $this->date,
        ];

        $preffixParts = array_map(function($str) {
            return '[' . mb_strtolower($str) . ']';
        }, $preffixParts);

        $preffix = implode('', $preffixParts);

        Log::$channel($preffix . ' ' . $message, $context);
    }
}
