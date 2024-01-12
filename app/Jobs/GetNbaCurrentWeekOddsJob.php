<?php

namespace App\Jobs;

use App\Models\Game;
use App\Traits\WebScrapingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class GetNbaCurrentWeekOddsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    use WebScrapingTrait;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $this->getNbaWeekMatches();
    }

    public function getNbaWeekMatches(): void
    {
        $this->log('debug', 'Obtendo jogos da semana...');

        $contents = $this->getUrlContents('basketball/usa/nba');

        $crawler = new Crawler($contents);

        $children = $crawler->filter('.ml__wrap')->children();

        $matches = [];

        $currentDate = null;

        foreach ($children as $child) {

            $crawler = new Crawler($child);

            // Obtém o valor do atributo 'class'
            $class = $crawler->attr('class');

            // Verifica se a classe 'match-list-headline-league' está presente
            if (strpos($class, 'match-list-headline-league') !== false) {

                $text = $crawler->text();

                $currentDate = $this->getDateYmd($text);

                continue;
            }

            $home = $crawler->filter('.match-team__name')
                ->eq(0)
                ->text();

            $away = $crawler->filter('.match-team__name')
                ->eq(1)
                ->text();

            $url = $crawler->filter('a.match-url')
                ->eq(0)
                ->attr('href');

            $time = $crawler->filter('.match-date__time span');

            $time = $time->count() > 0 ? $time->text() : null;

            $matches[] = [$home, $away, $url, $currentDate, $time];
        }

        $this->log('debug', 'Jogos obtidos: ' . count($matches));

        foreach ($matches as $match) {
            
            $game = Game::firstOrCreate([
                'home' => $match[0],
                'away' => $match[1],
                'url' => $match[2],
                'date' => $match[3],
            ]);

            if ($match[4]) {
                $game->time = $match[4];
                $game->save();
            }

            UpdateGameOddsJob::dispatch($game)
                ->onQueue('odds');
        }
    }

    private function getDateYmd(string $date): string
    {
        $dict = [
            'Jan' => '01',
            'Feb' => '02',
            'Mar' => '03',
            'Apr' => '04',
            'May' => '05',
            'Jun' => '06',
            'Jul' => '07',
            'Aug' => '08',
            'Sep' => '09',
            'Oct' => '10',
            'Nov' => '11',
            'Dec' => '12',
        ];

        $parts = explode(' ', $date);

        $day = $parts[0];
        $month = $dict[$parts[1]];
        $year = date('Y');

        return $year . '-' . $month . '-' . $day;
    }

    private function log(string $channel, string $message, array $context = []): void
    {
        Log::$channel('[NBA] ' . $message, $context);
    }
}
