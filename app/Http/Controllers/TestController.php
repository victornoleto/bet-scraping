<?php

namespace App\Http\Controllers;

use App\Jobs\GetNbaCurrentWeekOddsJob;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        GetNbaCurrentWeekOddsJob::dispatch();
    }
}
