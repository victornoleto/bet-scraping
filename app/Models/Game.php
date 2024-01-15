<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Game extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getMatchKey(): string
    {
        $parts = explode('-', $this->url);

        return end($parts);
    }

    public function scopeGetPlausibleOdds(Builder $query, Request $request)
    {
        $query
            ->where('start_at', '>=', now());

        $query->join('odds', 'games.id', '=', 'odds.game_id')
            ->join('bookmakers', 'odds.bookmaker_id', '=', 'bookmakers.id');

        $ratio = $request->get('ratio') ?? 2;

        $minOdd = $request->get('min_odd');

        $maxOdd = $request->get('max_odd');

        $query->where(function ($q) use ($ratio, $minOdd, $maxOdd) {

            $keys = [
                'home_odd',
                'away_odd',
            ];

            for ($i = 0; $i < 2; $i++) {

                if ($i == 1) {
                    $keys = array_reverse($keys);
                }

                list($a, $b) = $keys;

                $q->orWhere(function ($q1) use ($ratio, $minOdd, $maxOdd, $a, $b) {

                    $q1->whereRaw("($a * $ratio) < $b");

                    if ($minOdd) {
                        $q1->where($a, '>=', $minOdd);
                    }

                    if ($maxOdd) {
                        $q1->where($a, '<=', $maxOdd);
                    }

                });
            }

        });

        $selectedBookmakerIds = $request->get('bookmaker_id', [3]);

        $query->where('bookmakers.id', $selectedBookmakerIds);

        $query->select(
            'games.*',
            'bookmakers.name as bookmaker_name',
            'odds.home_odd',
            'odds.away_odd',
            'odds.draw_odd'
        );
    }
}
