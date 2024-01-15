<?php

namespace Database\Seeders;

use App\Enums\SportEnum;
use App\Models\Sport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $instances = SportEnum::getInstances();

        $data = [];

        foreach ($instances as $instance) {

            $data[] = [
                'id' => $instance->value,
                'name' => $instance->description,
                'slug' => $instance->key
            ];
        }

        Sport::insert($data);
    }
}
