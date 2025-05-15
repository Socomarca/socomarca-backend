<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Price;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            SubcategorySeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
            PriceSeeder::class,
        ]);
    }

}
