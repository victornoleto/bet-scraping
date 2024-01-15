<?php

namespace App\Console\Commands;

use App\Models\Bookmaker;
use App\Services\OddspediaService;
use Illuminate\Console\Command;

class UpdateBookmakersCommand extends Command
{
    protected $signature = 'app:update-bookmakers';

    protected $description = 'Atualizar casas de apostas';

    public function handle()
    {
        $serverBookmakers = $this->getServerBookmakers();

        foreach ($serverBookmakers as $row) {

            Bookmaker::updateOrCreate(
                [
                    'server_id' => $row['id']
                ],
                [
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'order' => $row['order'],
                    'rating' => $row['rating'],
                    'user_rating' => $row['user_rating'],
                ]
            );
        }
    }

    private function getServerBookmakers(): array
    {
        $service = new OddspediaService();

        $bookmakers = $service->getBookmakers();

        return $bookmakers;
    }
}
