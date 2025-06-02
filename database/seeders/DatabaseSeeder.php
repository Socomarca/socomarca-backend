<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RegionSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            PriceSeeder::class,
            ProductSeeder::class,
            PaymentMethodSeeder::class,
        ]);
    }

}
