<?php

namespace App\Models;

use App\Jobs\AlertNotifyJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Alert extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    public static function boot() {

        parent::boot();

        static::created(function (Alert $alert) {

            if ($alert->mustNotify()) {
                AlertNotifyJob::dispatch($alert)->onQueue('alert-notify');
            }

        });
    }

    public static function getUnsavedAlerts(int $gameId, int $bettingMarketId, string $refreshedAt): array
    {
        /* $enabledBookmakers = Bookmaker::query()
            ->where('enabled', true)
            ->get()
            ->toArray();

        $enabledBookmakersIds = array_column($enabledBookmakers, 'id');

        $enabledBookmakersInSql = implode(',', $enabledBookmakersIds); */

        $enabledBookmakersInSql = '1,2,5,7,8,10,11,12,25';

        $sql = "
        select
            o1.game_id, o1.betting_market_id, o1.period, o1.alternative,
            o1.id as o1_id, o1.bookmaker_id as o1_bookmaker_id, o1.o1 as o1,
            o2.id as o2_id, o2.bookmaker_id as o2_bookmaker_id, o2.o2 as o2,
            (((o1.o1*o2.o2)/(o1.o1+o2.o2)) - 1) * 100 as profit_percentage
            from odds o1
            join odds o2 on 
                o2.game_id = o1.game_id
                and o2.betting_market_id = o1.betting_market_id
                and o2.period = o1.period
                and o2.alternative = o1.alternative
                and o1.refreshed_at = o2.refreshed_at
                and o2.id != o1.id
                and o1.bookmaker_id in ($enabledBookmakersInSql)
                and o2.bookmaker_id in ($enabledBookmakersInSql)
            where
                o1.o1 > 1 and o1.o2 > 1
                and o2.o1 > 1 and o2.o2 > 1
                and
                (
                    o1.o1 > (o2.o2 / (o2.o2 - 1)) or
                    o2.o2 > (o1.o1 / (o1.o1 - 1))
                )
            and o1.game_id = $gameId
            and o1.betting_market_id = $bettingMarketId
            and o1.refreshed_at = '$refreshedAt'";

        $result = DB::select($sql);

        return $result;
    }

    public function mustNotify(): bool
    {
        return $this->profit_percentage > 2;
    }
}
