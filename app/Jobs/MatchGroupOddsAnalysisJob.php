<?php

namespace App\Jobs;

use App\Models\Game;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MatchGroupOddsAnalysisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Game $match, private array $group)
    {
        //
    }

    public function handle(): void
    {
        $groups = $this->getGroupedOdds();

        foreach ($groups as $period => $group) {

            foreach ($group as $alternative => $odds) {

                $highestO1Odd = null;
                $highestO2Odd = null;

                foreach ($odds as $odd) {

                    $o1 = $odd['odds'][0];
                    $o2 = $odd['odds'][1];

                    if ($highestO1Odd === null || $o1 > $highestO1Odd['odds'][0]) {
                        $highestO1Odd = $odd;
                    }

                    if ($highestO2Odd === null || $o2 > $highestO2Odd['odds'][1]) {
                        $highestO2Odd = $odd;
                    }
                }

                $data = [
                    'period' => $period,
                    'alternative' => $alternative === 'main' ? null : $alternative,
                    'o1' => [
                        'bookmaker' => $highestO1Odd['bookmaker'],
                        'odd' => floatval($highestO1Odd['odds'][0]),
                        'status' => $highestO1Odd['status'],
                        'payout' => $highestO1Odd['payout'],
                    ],
                    'o2' => [
                        'bookmaker' => $highestO2Odd['bookmaker'],
                        'odd' => floatval($highestO2Odd['odds'][1]),
                        'status' => $highestO2Odd['status'],
                        'payout' => $highestO2Odd['payout'],
                    ],
                ];

                $o1 = $data['o1']['odd'];
                $o2 = $data['o2']['odd'];

                if ($this->checkOddsAlertTrigger($o1, $o2)) {
                    
                    $data['group'] = $this->group['name'];
                    $data['oddsnames'] = $this->group['oddsnames'];
                    
                    // Enviar alerta
                    MatchOddsAlertJob::dispatch($this->match, $data)
                        ->onQueue('odds-alert');
                }
            }
        }
    }

    private function getGroupedOdds(): array
    {
        $groups = [];

        foreach ($this->group['odds'] as $odds) {

            $period = $odds['period'];

            $alternative = $odds['alternative'] ?? 'main';

            if (!isset($groups[$period])) {
                $groups[$period] = [];
            }

            if (!isset($groups[$period][$alternative])) {
                $groups[$period][$alternative] = [];
            }

            $groups[$period][$alternative][] = $odds;
        }

        return $groups;
    }

    private function checkOddsAlertTrigger(float $o1, float $o2): bool
    {
        return $o1 >= 2.0 && $o2 >= 2.0;
    }
}
