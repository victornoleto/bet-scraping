<?php

namespace App\Jobs;

use App\Models\Game;
use App\Services\OddspediaFootballService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateFootballMatchOddsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Game $match)
    {
    }
    
    public function handle(): void
    {
        $service = new OddspediaFootballService($this->match);

        $groupOddsFunctions = [
            //'getFullTimeResultOdds',
            'getAsianHandcapOdds',
            'getTotalGoalsOdds',
            'getOddOrEvenOdds',
            'getBothTeamToScoreOdds',
            'getTotalCornersOdds',
        ];

        foreach ($groupOddsFunctions as $function) {

            $result = $service->$function();

            $this->log('debug', 'Odds obtidas para "' . $result['name'] . '": ' . count($result['odds']));

            // Salvar odds no banco de dados
            // TODO ...

            if (count($result['oddsnames']) === 2) {
            
                // Analisar grupo de odds
                MatchGroupOddsAnalysisJob::dispatch($this->match, $result)
                    ->onQueue('odds-analysis');
            }

            usleep(100); // Dormir para não sobrecarregar o servidor
        }
    }

    private function log(string $channel, string $message, array $context = []): void
    {
        Log::$channel('[FootballOdds][' . $this->match->id . '] ' . $message, $context);
    }
}
