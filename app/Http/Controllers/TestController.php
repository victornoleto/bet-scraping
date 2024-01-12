<?php

namespace App\Http\Controllers;

use App\Jobs\GetNbaCurrentWeekOddsJob;
use App\Models\Bookmaker;
use App\Models\Game;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request)
    {
        $bookmakers = Bookmaker::all();

        $ratio = $request->get('ratio', 2);

        $odds = Game::getPlausibleOdds($request)
            ->get()
            ->toArray();

        return view('plausible-odds', [
            'bookmakers' => $bookmakers,
            'odds' => $odds,
        ]);
    }
}
