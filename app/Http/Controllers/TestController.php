<?php

namespace App\Http\Controllers;

use App\Jobs\GetNbaCurrentWeekOddsJob;
use App\Models\Bookmaker;
use App\Models\Game;
use App\Services\OddspediaFootballService;
use App\Services\OddspediaService;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request)
    {
        /* $bookmakers = Bookmaker::all();

        $ratio = $request->get('ratio', 2);

        $odds = Game::getPlausibleOdds($request)
            ->get()
            ->toArray();

        return view('plausible-odds', [
            'bookmakers' => $bookmakers,
            'odds' => $odds,
        ]); */
    }

    public function test()
    {
        /* $mainService = new OddspediaService();

        $matches = $mainService->getSportMatches('football', '2024-01-15', false);

        dd($matches); */

        $game = Game::find(60);

        $service = new OddspediaFootballService($game);

        $odds = $service->getTotalGoalsOdds();

        dd($odds);
    }
}
