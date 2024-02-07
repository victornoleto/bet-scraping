<?php

namespace App\Console\Commands;

use App\Jobs\UpdateGamesJob;
use App\Models\Sport;
use Illuminate\Console\Command;

class UpdateGamesCommand extends Command
{
    protected $signature = 'app:update-games {sport?} {--date=} {--end-date=} {--increment-days=0} {--popular-league-only=0} {--sync=0} {--every-sport=0}';

    protected $description = 'Atualizar jogos de um esporte';

    public function handle()
    {
        $sports = $this->getSports();

        if (empty($sports)) {
            return $this->error('Informe um esporte ou use a opção --every-sport para atualizar todos os esportes.');
        }

        $dates = $this->getDatesRange();

        foreach ($sports as $sport) {

            foreach ($dates as $date) {

                $args = [
                    $sport,
                    $date,
                    $this->option('popular-league-only')
                ];

                $job = new UpdateGamesJob(...$args);

                if ($this->option('sync')) {
                    $job->handle();

                } else {
                    dispatch($job->onQueue('update-games'));
                }
            }
        }
    }

    private function getDatesRange(): array
    {
        $date = $this->option('date') ?? date('Y-m-d');

        $endDate = $this->option('end-date') ?? date('Y-m-d', strtotime($date . ' + ' . $this->option('increment-days') . ' days'));

        $dates = [];

        while (true) {
                
            $dates[] = $date;

            $date = date('Y-m-d', strtotime($date . ' + 1 day'));

            if ($date > $endDate) {
                break;
            }
        }

        return $dates;
    }

    private function getSports(): array
    {
        $sport = $this->argument('sport');

        if ($sport) {
            return [$sport];
        }

        if ($this->option('every-sport')) {
            
            return Sport::get()
                ->pluck('slug')
                ->toArray();
        }

        return [];
    }
}
