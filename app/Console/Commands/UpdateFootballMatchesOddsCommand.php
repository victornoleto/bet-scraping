<?php

namespace App\Console\Commands;

use App\Jobs\UpdateFootballMatchOddsJob;
use App\Models\Game;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateFootballMatchesOddsCommand extends Command
{
    protected $signature = 'app:update-football';

    public function handle()
    {
        $keepAlive = config('app.match_keepalive_minutes');

        $matches = Game::query()
            ->where('sport', 'football')
            ->where('start_at', '>', now()->subMinutes($keepAlive))
            ->get();

        foreach ($matches as $match) {

            $this->log('info', 'Atualizando odds da partida: ' . $match->id);

            // Atualizar e analisar odds
            UpdateFootballMatchOddsJob::dispatch($match)
                ->onQueue('odds');
        }
    }

    private function log(string $channel, string $message, array $context = []): void
    {
        Log::$channel('[UpdateFootballMatchesOdds] ' . $message, $context);
    }
}
