<?php

namespace App\Http\Controllers;

use App\Jobs\GetNbaCurrentWeekOddsJob;
use App\Models\Bookmaker;
use App\Models\Game;
use App\Services\OddspediaFootballService;
use App\Services\OddspediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    public function index(Request $request)
    {
        $service = new OddspediaService();

        $games = $service->getSportGames(
            'football',
            now()->format('Y-m-d'),
            false
        );

        dd($games);
    }

    public function tips()
    {
        $service = new OddspediaService();
        /*
        $tips = $service->getTipsByTipster();

        dd($tips); */

        /* $tipsJson = Storage::get('tips-domadores-ciub-202401171610.json');

        $tips = json_decode($tipsJson, true);
        */

        $tips = $service->getTipsByConsensus(true);

        $tips = array_filter($tips, function ($tip) {

            $md = $tip['match']['md'];

            return
                strpos($md, '2024-01-20') !== false;

        });

        return view('consensus-tips', [
            'tips' => $tips,
        ]);
    }
}
