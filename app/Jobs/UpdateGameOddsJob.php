<?php

namespace App\Jobs;

use App\Models\Bookmaker;
use App\Models\Game;
use App\Models\Odd;
use App\Models\OddHistory;
use App\Traits\WebScrapingTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class UpdateGameOddsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use WebScrapingTrait;

    public function __construct(public Game $game)
    {
    }

    public function handle(): void
    {
        try {

            $odds = $this->getBookmakersOdds();

            usleep(250); // Dormir por alguns milisegundos para não sobrecarregar o servidor

            $oddsCreatedAt = now();
    
            foreach ($odds as $odd) {
    
                $bookmakerName = $odd[0];
                $odds = $odd[1];
    
                $bookmaker = Bookmaker::firstOrCreate([
                    'name' => $bookmakerName
                ]);
                
                $oddsData = [
                    'home_odd' => $odds[0],
                    'away_odd' => $odds[1],
                    'draw_odd' => $odds[2] ?? null
                ];
    
                $this->log('debug', $bookmakerName, $oddsData);
    
                $odd = Odd::updateOrCreate(
                    [
                        'game_id' => $this->game->id,
                        'bookmaker_id' => $bookmaker->id
                    ],
                    $oddsData
                );
    
                OddHistory::create(
                    array_merge($oddsData, [
                        'odd_id' => $odd->id,
                        'created_at' => $oddsCreatedAt
                    ])
                );
            }
            
        } catch (\Exception $e) {

            $this->log('error', 'Erro ao atualizar odds', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return;
        }

        if ($this->mustContinue()) {

            $refreshMinutes = config('app.odds_refresh_minutes');

            $this->log('debug', 'Atualizando odds novamente em ' . $refreshMinutes . ' minutos...');

            UpdateGameOddsJob::dispatch($this->game)
                ->delay(
                    now()->addMinutes($refreshMinutes)
                )
                ->onQueue('odds');
        }
    }

    private function getBookmakersOdds(): array
    {
        $contents = $this->getUrlContents($this->game->url);
    
        $crawler = new Crawler($contents);
    
        $rows = $crawler->filter('.eoc-table__row:not(.eoc-table__row--labels)');
    
        $bookmakers = [];
    
        foreach ($rows as $row) {
    
            $crawler = new Crawler($row);
    
            $bookmaker = $crawler->filter('.eoc-table__row__bookmaker span')
                ->eq(1)
                ->text();
    
            $oddElements = $crawler->filter('.eoc-table__row__odd');
            
            $odds = [];

            $indexes = [
                0, // home
                count($oddElements) == 2 ? 1 : 2, // away
                count($oddElements) == 2 ? null : 1, // draw
            ];

            foreach ($indexes as $index) {

                if ($index === null) {
                    $odds[] = null;
                    continue;
                }

                $oddElement = $oddElements->eq($index);

                $odd = $oddElement->text();

                $odds[] = floatval($odd);
            }

            $bookmakers[] = [$bookmaker, $odds];
        }
    
        return $bookmakers;
    }

    private function mustContinue(): bool
    {
        $date = new Carbon($this->game->date);

        $stopUpdateOddsAfter = config('app.stop_update_odds_after');

        $date->addMinutes($stopUpdateOddsAfter);

        $now = Carbon::now();

        return $date->isAfter($now);
    }

    private function log(string $channel, string $message, array $context = []): void
    {
        Log::$channel('[odds][' . $this->game->id . '] ' . $message, $context);
    }
}
