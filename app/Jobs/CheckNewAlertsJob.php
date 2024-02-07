<?php

namespace App\Jobs;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckNewAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $gameId,
        public int $bettingMarketId,
        public string $refreshedAt
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $unsavedAlerts = Alert::getUnsavedAlerts(
            $this->gameId,
            $this->bettingMarketId,
            $this->refreshedAt
        );

        $newAlertIds = [];

        foreach ($unsavedAlerts as $data) {

            $alert = Alert::where([
                'o1_id' => $data->o1_id,
                'o2_id' => $data->o2_id,
            ])
            ->first();

            if ($alert) {
                continue;
            }

            $alert = Alert::create([
                'game_id' => $data->game_id,
                'betting_market_id' => $data->betting_market_id,
                'period' => $data->period,
                'alternative' => $data->alternative,
                'o1_id' => $data->o1_id,
                'o1_bookmaker_id' => $data->o1_bookmaker_id,
                'o1' => $data->o1,
                'o2_id' => $data->o2_id,
                'o2_bookmaker_id' => $data->o2_bookmaker_id,
                'o2' => $data->o2,
                'profit_percentage' => $data->profit_percentage,
                'refreshed_at' => $this->refreshedAt,
                'created_at' => now()
            ]);

            $newAlertIds[] = $alert->id;
        }

        Log::debug('[ALERTS] Novos alertas criados: ' . count($newAlertIds));
    }
}
