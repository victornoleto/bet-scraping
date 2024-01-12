<?php

namespace App\Console\Commands;

use App\Jobs\GetNbaCurrentWeekOddsJob;
use Illuminate\Console\Command;

class GetNbaCurrentWeekOddsCommand extends Command
{
    protected $signature = 'app:get-nba-current-week-odds';

    protected $description = 'Obter odds das partidas da semana atual da NBA';

    public function handle()
    {
        GetNbaCurrentWeekOddsJob::dispatch()
            ->onQueue('get-nba-current-week-odds');
    }
}
