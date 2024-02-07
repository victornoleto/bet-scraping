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

        return view('alerts', [
            'alerts' => $alerts
        ]);
    }
}
