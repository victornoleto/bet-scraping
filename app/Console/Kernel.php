<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Baixar partidas de futebol (ligas populares) do dia seguinte
        $schedule->command('app:get-daily-matches football --tomorrow=1')
            ->daily();

        // Baixar partidas de basquete (NBA) do dia seguinte
        $schedule->command('app:get-daily-matches basketball --tomorrow=1')
            ->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
