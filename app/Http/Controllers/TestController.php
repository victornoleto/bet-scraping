<?php

namespace App\Http\Controllers;

use App\Jobs\GetNbaCurrentWeekOddsJob;
use App\Models\Game;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request)
    {
        $scale = $request->get('scale', 2);

        $odds = Game::getPlausibleOdds($scale)
            ->get()
            ->toArray();

        return view('plausible-odds', [
            'odds' => $odds,
        ]);
    }
}
