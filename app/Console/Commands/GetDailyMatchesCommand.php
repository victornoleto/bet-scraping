<?php

namespace App\Console\Commands;

use App\Jobs\GetDailyMatchesJob;
use Illuminate\Console\Command;

class GetDailyMatchesCommand extends Command
{
    protected $signature = 'app:get-daily-matches {sport?} {date?} {--popular-league-only=1} {--tomorrow=0}';

    protected $description = 'Obter odds das partidas de futebol de determinada data';

    public function handle()
    {
        $sport = $this->argument('sport') ?? 'football';

        $tomorrow = $this->option('tomorrow') == 1;

        $date = $this->argument('date') ?? ($tomorrow ? date('Y-m-d', strtotime('+1 day')) : date('Y-m-d'));

        $popularLeagueOnly = $this->option('popular-league-only') == 1;
        
        GetDailyMatchesJob::dispatch($sport, $date, $popularLeagueOnly)
            ->onQueue('matches');
    }
}
