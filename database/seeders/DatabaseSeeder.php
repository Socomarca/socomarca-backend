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
        Category::factory(5)->create();
        Brand::factory(5)->create();

        Subcategory::factory(10)->create();

        Product::factory(20)->create()->each(function ($product) {
            Price::factory()->create([
                'product_id' => $product->id
            ]);
        });
    }
}
