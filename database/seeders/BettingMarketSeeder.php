<?php

namespace Database\Seeders;

use App\Enums\SportEnum;
use App\Models\BettingMarket;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BettingMarketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            SportEnum::football => [
                'Full Time Result' => 1,
                'Total Goals' => 4,
                //'Asian Handicap' => 3,
                'Both Teams To Score' => 11,
                //'Total Corners' => 63,
                //'Odd or Even' => 10,
            ],
            SportEnum::basketball => [
                'Moneyline' => 2,
                //'Spread' => 3,
            ],
            /* SportEnum::volleyball => [
                'Match Winner' => 2,
                'Sets/Points Total' => 4,
                'Sets/Points Handicap' => 3,
                'Odd or Even' => 10,
            ],
            SportEnum::tennis => [
                'Match Winner' => 2,
                'Total Sets/Games' => 4,
                'Match Handicap' => 3,
                'Odd or Even' => 10,
            ] */
        ];

        foreach ($data as $sportId => $list) {

            foreach ($list as $name => $oddGroupId) {
                
                BettingMarket::create([
                    'sport_id' => $sportId,
                    'name' => $name,
                    'odd_group_id' => $oddGroupId,
                ]);
            }
        }
    }
}
