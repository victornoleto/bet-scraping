<?php

namespace App\Services;

use App\Enums\SportEnum;
use App\Models\ServerRequest;
use App\Traits\WebScrapingTrait;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OddspediaService
{
    use WebScrapingTrait;

    public static string $baseUrl = 'https://oddspedia.com';

    public function __construct()
    {
    }

    public function getBookmakers(): array
    {
        $url = '/api/v1/getBookmakers';

        $query = [
            'geoCode' => 'BR',
            'geoState' => '',
            'language' => 'en',
        ];

        $headers = [
            'referer' => 'https://oddspedia.com/football',
        ];

        $contents = $this->request($url, $query, $headers);

        $result = json_decode($contents, true);

        return $result['data'];
    }

    public function getSportGames(string $sport, string $date, bool $popularLeagueOnly = true): array
    {
        $afterDate = date('Y-m-d', strtotime($date . ' +1 day'));

        $page = 1;

        $games = [];

        while (true) {

            $query = [
                'excludeSpecialStatus' => '0',
                'sortBy' => 'default',
                'perPageDefault' => '50',
                'startDate' => $date . 'T03:00:00Z',
                'endDate' => $afterDate . 'T02:59:59Z',
                'geoCode' => 'BR',
                'status' => 'all',
                'sport' => $sport,
                'popularLeaguesOnly' => $popularLeagueOnly ? '1' : '0',
                'page' => $page,
                'perPage' => '500',
                'language' => 'en',
            ];
    
            $headers = [
                'referer' => 'https://oddspedia.com/' . $sport,
            ];
    
            $url = '/api/v1/getMatchList';

            $contents = $this->request($url, $query, $headers);

            $result = json_decode($contents, true);

            $pageGames = $this->getPageGames($result['data']);

            $games = array_merge($games, $pageGames);

            if ($result['data']['total_pages'] === $page) {
                break;
            }

            $page++;
        }

        return $games;
    }

    public function getBettingMarketOdds(string $matchKey, string $gameUrl, int $groupId): array
    {
        $query = [
            'wettsteuer' => '0',
            'geoCode' => 'BR',
            'bookmakerGeoCode' => 'BR',
            'bookmakerGeoState' => '',
            'matchKey' => $matchKey,
            'oddGroupId' => $groupId,
            'inplay' => '0',
            'language' => 'en',
        ];

        $headers = [
            'referer' => self::$baseUrl . $gameUrl,
        ];

        $contents = $this->request('/api/v1/getMatchOdds', $query, $headers);

        $result = json_decode($contents, true);

        $dataKey = isset($result['data']['prematch']) ? 'prematch' : 'inplay';

        foreach ($result['data'][$dataKey] as $row) {

            if ($row['id'] == $groupId) {
                return $this->formatBettingMarketOddsResult($row);
            }
        }

        return [];
    }

    public function getTipsByTipster(string $userId = 'domadores-ciub', string $status = 'active,pending', bool $cache = false): array
    {
        $url = '/api/v1/getTipsByTipster';

        $query = [
            'perPage' => 50,
            'sortBy' => 'date',
            'order' => 'DESC',
            'userId' => $userId,
            'status' => $status,
            'showFilters' => '0',
            'language' => 'br',
        ];

        $headers = [
            'referer' => self::$baseUrl . '/u/' . $userId,
        ];

        return $this->getTips($url, $query, $headers, $cache);
    }

    public function getTipsByConsensus(bool $cache = false): array
    {
        $url = '/api/v1/getTipsByConsensus';

        $query = [
            'q' => '',
            'perPage' => 50,
            'sortBy' => 'consensus_percentage',
            'order' => 'DESC',
            'sportId' => '',
            'leagueId' => '',
            'matchId' => '',
            'marketId' => '',
            'consensusPercentage' => '51.00,100.00',
            'tipsAmount' => '3.00,100000.00',
            'timePeriod' => 'all',
            'tipsterRank' => 'all',
            'onlyFollowedUsers' => 'false',
            'wettsteuer' => '0',
            'geoCode' => 'BR',
            'geoState' => '',
            'language' => 'en',
        ];

        $headers = [
            'referer' => self::$baseUrl . '/tips'
        ];

        return $this->getTips($url, $query, $headers, $cache);
    }

    private function getTips(string $url, array $query, array $headers, bool $cache = false): array
    {
        $hash = md5($url . '_'. json_encode($query));

        $cacheFilename = 'tips-' . $hash . '_' . date('YmdH') . '.json';

        if ($cache && Storage::exists($cacheFilename)) {
            return json_decode(Storage::get($cacheFilename), true);
        }

        $page = 1;

        $tips = [];

        while (true) {

            $query['page'] = $page;

            $contents = $this->request($url, $query, $headers);

            $result = json_decode($contents, true);

            $tips = array_merge($tips, $result['data']);

            if ($page == $result['total_pages']) {
                break;
            }

            $page++;
        }

        Storage::put($cacheFilename, json_encode($tips));

        return $tips;
    }

    public function request(string $url, array $query = [], array $headers = []): string
    {
        $sleepTime = $this->checkPreRequestSleepTime();

        if ($sleepTime > 0) {
            sleep($sleepTime);
        }
        
        try {

            $t0 = microtime(true);

            $req = new ServerRequest([
                'url' => $url,
                'query' => json_encode($query),
                'headers' => json_encode($headers),
                'started_at' => date('Y-m-d H:i:s'),
            ]);

            $contents = $this->getUrlContents($url, $query, $headers);

            $req->status_code = Response::HTTP_OK;
            
        } catch (ClientException $e) {
            $req->status_code = $e->getResponse()->getStatusCode();
            $req->error = $e->getMessage();
        }

        $req->finished_at = date('Y-m-d H:i:s');
        $req->elapsed_time = microtime(true) - $t0;
        $req->save();

        if ($req->status_code !== Response::HTTP_OK) {
            throw new \Exception($req->error, $req->status_code);
        }

        return $contents;
    }

    private function getPageGames(array $page): array
    {
        $categoryList = $page['categoryList'];

        $leagueList = $page['leagueList'];

        $games = [];

        foreach ($page['matchList'] as $row) {

            $sportId = SportEnum::fromKey($row['sport_slug'])
                ->value;

            $game = [
                'sport_id' => $sportId,
                'server_id' => $row['id'],
                'ht' => $row['ht'],
                'ht_server_id' => $row['ht_id'],
                'at' => $row['at'],
                'at_server_id' => $row['at_id'],
                'url' => $row['uri'],
                'key' => getGameKeyFromUrl($row['uri']),
                'match_time' => utcToTz($row['md'], 'America/Sao_Paulo'),
                'category' => $categoryList[$row['category_id']]['slug'],
                'league' => $leagueList[$row['league_id']]['slug'],
            ];

            $games[] = $game;
        }

        return $games;
    }

    private function checkPreRequestSleepTime(): int
    {
        $lastRequests = ServerRequest::query()
            ->select('status_code')
            ->where('finished_at', '>=', now()->subSeconds(60))
            ->orderBy('id', 'desc')
            ->get();

        if (!$lastRequests->isEmpty()) {

            if ($lastRequests[0]->status_code == Response::HTTP_TOO_MANY_REQUESTS) {
    
                Log::debug('[OddspediaService] Última requisição triggou o rate limit. Dormindo por 30 segundos.');
    
                return 30;
            
            } elseif ($lastRequests->count() >= 60) {

                Log::debug('[OddspediaService] Muitas requisições nos últimos 60 segundos. Dormindo por 10 segundos.');
    
                return 10;
            }
        }

        return 0;
    }

    private function formatBettingMarketOddsResult(array $row): array
    {
        $group = [
            'id' => $row['id'],
            'name' => $row['name'],
            'oddsnames' => $row['oddsnames'],
            'odds' => [],
        ];

        foreach ($row['periods'] as $period) {

            $periodOdds = $period['odds'];

            $oddsList = [];

            if (isset($periodOdds['alternative']) && isset($periodOdds['main'])) {

                $keys = ['main', 'alternative'];

                foreach ($keys as $key) {

                    if (isset($periodOdds[$key])) {
    
                        foreach ($periodOdds[$key] as $keyList) {
    
                            $keyListOdds = $keyList['odds'];
    
                            foreach ($keyListOdds as $i => $keyListOdd) {
                                $keyListOdds[$i]['alternative'] = $keyList['name'];
                            }

                            $oddsList = array_merge($oddsList, array_values($keyListOdds));
                        }
                    }
                }

            } else {
                $oddsList = $periodOdds;
            }

            foreach ($oddsList as $odd) {

                $arr = [
                    'period' => $period['name'],
                    'alternative' => $odd['alternative'] ?? null,
                    'bookmaker' => $odd['bookie_slug'],
                    'odds' => [],
                    'status' => $odd['status'],
                    'payout' => $odd['payout'],
                ];

                foreach ($row['oddsnames'] as $index => $name) {
                    $arr['odds'][] = $odd['o' . ($index+1)];
                }

                $group['odds'][] = $arr;
            }
        }

        return $group;
    }
}
