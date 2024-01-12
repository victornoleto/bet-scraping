<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scopeGetPlausibleOdds(Builder $query, int $scale)
    {
        $query
            ->where('date', '>=', date('Y-m-d'));

        $query->join('odds', 'games.id', '=', 'odds.game_id')
            ->join('bookmakers', 'odds.bookmaker_id', '=', 'bookmakers.id');

        $query->where(function($q) use ($scale) {
            
            $q->whereRaw("(home_odd * $scale) < away_odd")
                ->orWhereRaw("(away_odd * $scale) < home_odd");

        });

        $query->select(
            'games.*',
            'bookmakers.name as bookmaker_name',
            'odds.home_odd',
            'odds.away_odd',
            'odds.draw_odd'
        );
    }
}
