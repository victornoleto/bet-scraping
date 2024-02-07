<?php

namespace App\View\Components;

use App\Models\Alert;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BookmakerOddLink extends Component
{
    public string $url;
    
    private string $bookmaker;

    public function __construct(public Alert $alert, public int $odd)
    {
        $this->bookmaker = $this->alert[$odd == 1 ? 'o1_bookmaker_slug' : 'o2_bookmaker_slug'];
        $this->url = $this->getUrl();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.bookmaker-odd-link');
    }

    private function getUrl(): string
    {
        $ht = explode(' ', $this->alert->ht)[0];

        switch ($this->bookmaker) {
            case "stake-com":
                return 'https://stake.com/pt/sports/soccer';

            case "fezbet":
                return 'https://fezbet.com/br/sport';

            case "powbet":
                return 'https://powbet.com/br/sports';

            case "megapari-sport":
                return 'https://megapari.com/pt/live/football';

            case "1xbet":
                return 'https://br.1xbet.com/line';

            case "betwinner":
                return 'https://betwinner.com/br';

            case "20bet":
                return 'https://20bet.com/br';

            case "betano":
                return 'https://br.betano.com/';

            case "bc-game-sport":
                return '';

            case "bet365":
                return 'https://www.bet365.com/#/AS/B1/';

            case "pinnacle":
                return "https://www.pinnacle.com/pt/search/$ht";

            case "22bet":
                return 'https://22bets.me/br';

            case "betibet":
                return 'https://www.betibet.com/pt-BR';

            case "bwin":
                return '';

            case "bzeebet":
                return '';

            case "gg-bet":
                return '';

            case "leovegas-sport":
                return '';

            case "bettilt":
                return '';

            case "mystake":
                return '';

            case "dafabet":
                return '';

            case "vave":
                return '';

            case "nine-casino":
                return '';

            case "snatch-casino":
                return '';

            case "31bet":
                return '';

            case "betway":
                return '';

            case "bluechip":
                return '';

            case "galera-bet":
                return '';

            case "sportsbet-io":
                return '';

            case "midnite":
                return '';

            case "betsafe":
                return '';

            case "sportaza":
                return '';

            case "ivibet":
                return '';

            case "betkwiff":
                return '';

            case "betfair":
                return '';

            case "marathonbet":
                return '';

            case "roobet":
                return '';

            case "mostbet":
                return '';

            case "weltbet":
                return '';

            case "kto":
                return '';

            case "rivalry":
                return '';

            case "rolletto":
                return '';

            case "betsul":
                return '';

            case "bet7":
                return '';

            case "betboo-sport":
                return '';

            case "amuletobet":
                return '';

            case "jackbit":
                return '';

            case "dafabet-latam":
                return '';

            case "zebet":
                return '';

            case "comeon":
                return '';

            case "adjarabet":
                return '';

            case "lsbet":
                return '';

            case "mr-green-sport":
                return '';

            case "blaze":
                return '';

            case "instabet":
                return '';

            case "bet9":
                return '';

            case "tonybet":
                return '';

            case "pin-up":
                return '';

            case "betmotion-sport":
                return '';

            case "betobet":
                return '';

            case "bet90":
                return '';

            case "betwarrior":
                return '';

            case "betcris":
                return '';

            case "betsson":
                return '';

            case "cloudbet":
                return '';

            case "goldenbet":
                return '';

            case "netbet":
                return '';

            case "parimatch":
                return '';

            case "4rabet":
                return '';

            case "suprabets":
                return '';

            case "rivalo":
                return '';

            case "freshbet":
                return '';

            case "loot-bet":
                return '';

            case "lvbet":
                return '';

            case "bodog":
                return '';

            case "bethard":
                return '';
            
            default:
                return '';
        }
    }
}
