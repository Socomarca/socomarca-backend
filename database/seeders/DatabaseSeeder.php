<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        if (app()->environment(['local', 'qa','testing'])) {
            $this->call([
                RegionSeeder::class,
                UserSeeder::class,
                //CategorySeeder::class,
                SubcategorySeeder::class,
                BrandSeeder::class,
                ProductSeeder::class,
                PaymentMethodSeeder::class,
                RolesAndPermissionsSeeder::class,
                OrderSeeder::class,
                CartItemSeeder::class,
            ]);

        }else{
            $this->call([
                RegionSeeder::class,
                PaymentMethodSeeder::class,
                RolesAndPermissionsSeeder::class,
            ]);
        }
    }

}
