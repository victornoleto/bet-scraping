<?php

namespace App\Services;

use App\Jobs\MatchGroupOddsAnalysisJob;
use App\Models\Game;

class OddspediaFootballService extends OddspediaService
{
    public function __construct(private Game $game)
    {
        parent::__construct();
    }

    public function getFullTimeResultOdds(): array
    {
        return $this->getGroupOdds(1);
    }

    public function getAsianHandcapOdds(): array
    {
        return $this->getGroupOdds(3);
    }

    public function getTotalGoalsOdds(): array
    {
        return $this->getGroupOdds(4);
    }

    public function getOddOrEvenOdds(): array
    {
        return $this->getGroupOdds(10);
    }

    public function getBothTeamToScoreOdds(): array
    {
        return $this->getGroupOdds(11);
    }

    public function getTotalCornersOdds(): array
    {
        return $this->getGroupOdds(63);
    }
}
