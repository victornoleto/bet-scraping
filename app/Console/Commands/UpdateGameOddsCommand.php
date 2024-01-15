<?php

namespace App\Console\Commands;

use App\Jobs\GameOddsAnalysisJob;
use App\Models\BettingMarket;
use App\Models\Bookmaker;
use App\Models\Game;
use App\Models\Odd;
use App\Services\OddspediaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateGameOddsCommand extends Command
{
    protected $signature = 'app:update-game-odds';

    private string $refreshId;

    private array $enabledBookmakers = [];

    private array $enabledBettingMarkets = [];

    public function handle()
    {
        $this->refreshId = uniqid();

        $games = Game::query()
            ->where('match_time', '>=', now()->subMinutes(90))
            ->orderBy('sport_id')
            ->orderBy('match_time')
            ->get();

        $this->enabledBookmakers = Bookmaker::query()
            ->where('enabled', true)
            ->get()
            ->toArray();

        $this->enabledBettingMarkets = BettingMarket::query()
            ->where('enabled', true)
            ->get()
            ->toArray();

        foreach ($games as $index => $game) {

            $t0 = microtime(true);

            $this->refreshGameOdds($game);

            $elapsedSecs = round(microtime(true) - $t0, 2);

            $message = sprintf(
                'Odds atualizadas em %s segundos (%s/%s)',
                $elapsedSecs,
                $index + 1,
                $games->count()
            );

            $this->log('info', $message, [$game->id]);
        }
    }

    private function refreshGameOdds(Game $game): void
    {
        $service = new OddspediaService($game);

        $bettingMarkets = array_filter($this->enabledBettingMarkets, function ($market) use ($game) {
            return $market['sport_id'] == $game->sport_id;
        });

        foreach ($bettingMarkets as $market) {

            try {

                $t0 = microtime(true);

                $result = $service->getBettingMarketOdds(
                    $game->key,
                    $game->url,
                    $market['odd_group_id']
                );

                if (empty($result) || empty($result['odds'])) {
                    continue;
                }

                $oddsList = array_filter($result['odds'], function ($row) {
                    return in_array($row['bookmaker'], array_column($this->enabledBookmakers, 'slug'));
                });

                foreach ($oddsList as $row) {
                    $this->saveGameBookmakerOdds($game->id, $market['id'], $row);
                }

                $elapsedSecs = round(microtime(true) - $t0, 2);

                $message = sprintf(
                    'Odds atualizadas em %s segundos',
                    $elapsedSecs,
                );

                $this->log('info', $message, [$game->id, $market['name']]);

                /* GameOddsAnalysisJob::dispatch($game, $market, $this->refreshId)
                    ->onQueue('game-odds-analysis'); */

            } catch (\Exception $e) {
                dd($e);
                $this->log('error', $e->getMessage(), [$game->id, $market['id']]);
            }
        }
    }

    private function saveGameBookmakerOdds(int $gameId, int $bettingMarketId, array $data): void
    {
        $bookmakerSlug = $data['bookmaker'];

        $bookmaker = array_filter($this->enabledBookmakers, function ($row) use ($bookmakerSlug) {
            return $row['slug'] == $bookmakerSlug;
        });

        $bookmaker = array_shift($bookmaker);

        $attributes = [
            'game_id' => $gameId,
            'bookmaker_id' => $bookmaker['id'],
            'betting_market_id' => $bettingMarketId,
            'period' => $data['period'],
            'alternative' => $data['alternative'],
            'status' => $data['status'],
            'payout' => $data['payout'],
            'refresh_id' => $this->refreshId,
            'refreshed_at' => now(),
        ];

        foreach ($data['odds'] as $index => $value) {
            $attributes['o' . ($index+1)] = $value;
        }

        Odd::create($attributes);
    }

    private function log(string $channel, string $message, array $prefixParts = [], array $context = []): void
    {
        array_unshift($prefixParts, 'update-game-odds');

        $prefix = getLogPrefix($prefixParts);

        Log::$channel($prefix . ' ' . $message, $context);
    }
}
