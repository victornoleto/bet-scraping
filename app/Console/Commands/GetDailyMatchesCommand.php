<?php

namespace App\Console\Commands;

use App\Jobs\GetDailyMatchesJob;
use Illuminate\Console\Command;

class GetDailyMatchesCommand extends Command
{
    protected $signature = 'app:get-daily-matches-command {sport?} {date?}';

    protected $description = 'Obter odds das partidas de futebol de determinada data';

    public function handle()
    {
        $sport = $this->argument('sport') ?? 'football';

        $date = $this->argument('date') ?? date('Y-m-d');
        
        GetDailyMatchesJob::dispatch($sport, $date)
            ->onQueue('get-daily-matches');
    }
}
