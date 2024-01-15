<?php

namespace App\Jobs;

use App\Models\Game;
use App\Services\OddspediaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateGamesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $sport,
        private string $date,
        private bool $popularLeagueOnly = true,
    )
    {
    }
    
    public function handle(): void
    {
        $service = new OddspediaService();

        $t0 = microtime(true);

        $games = $service->getSportGames(
            $this->sport,
            $this->date,
            $this->popularLeagueOnly
        );

        $elapsedSecs = round(microtime(true) - $t0, 2);

        $this->log('debug', count($games) . ' partidas obtidas em ' . $elapsedSecs . ' segundos');

        $this->saveGames($games);
    }

    private function saveGames(array $unsavedGames): void
    {
        $newOnes = 0;

        foreach ($unsavedGames as $row) {

            $game = Game::firstOrNew([
                'server_id' => $row['server_id']
            ]);

            $game->fill($row);

            $game->save();

            if ($game->wasRecentlyCreated) {
                
                $newOnes++;

                // Dispatch job to update game odds
                // TODO ...
            }
        }

        if ($newOnes > 0) {
            $this->log('debug', $newOnes . ($newOnes > 1 ? ' novas partidas criadas.' : ' nova partida criada.'));

        } else {
            $this->log('debug', 'Nenhuma nova partida criada.');
        }
    }

    private function log(string $channel, string $message, array $context = []): void
    {
        $prefixParts = [
            'update-games',
            $this->sport,
            $this->date
        ];

        $prefix = getLogPrefix($prefixParts);

        Log::$channel($prefix . ' ' . $message, $context);
    }
}
