<?php

namespace Database\Seeders;

use App\Models\Municipality;
use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Region::factory()->count(5)
            ->has(Municipality::factory()->count(2), 'municipalities')
                ->create();
    }
}
