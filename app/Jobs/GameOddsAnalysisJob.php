<?php

namespace App\Jobs;

use App\Models\BettingMarket;
use App\Models\Game;
use App\Models\Odd;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GameOddsAnalysisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Game $game, private array $market, private string $refreshId)
    {
        //
    }

    public function handle(): void
    {
        $odds = $this->getOdds();

        // TODO agrupar por period e alternative
    }

    private function getOdds(): array
    {
        $odds = Odd::query()
            ->where('game_id', $this->game->id)
            ->where('betting_market_id', $this->market['id'])
            ->where('refresh_id', $this->refreshId)
            ->get()
            ->toArray();

        return $odds;
    }
}
