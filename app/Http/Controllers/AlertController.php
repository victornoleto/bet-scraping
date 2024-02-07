<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $alerts = Alert::search($request)
            ->get();

        $alerts = $alerts->filter(function ($alert) {
            return $alert->rank == 1;
        });

        $categories = $alerts->pluck('category')
            ->unique()
            ->toArray();

        $leagues = $alerts->pluck('league')
            ->unique()
            ->toArray();

        $bookmakers = array_unique(
            array_merge(
                $alerts->pluck('o1_bookmaker_name')->unique()->toArray(),
                $alerts->pluck('o2_bookmaker_name')->unique()->toArray(),
            )
        );

        return view('alerts', [
            'alerts' => $alerts,
            'categories' => $categories,
            'leagues' => $leagues,
            'bookmakers' => $bookmakers,
        ]);
    }
}
