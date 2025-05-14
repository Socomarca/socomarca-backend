<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        \App\Models\Category::factory(5)->create()->each(function ($category) {
            \App\Models\Subcategory::factory(3)->create([
                'category_id' => $category->id,
            ]);
        });

        \App\Models\Brand::factory(5)->create();

        \App\Models\Product::factory(20)->create()->each(function ($product) {
            \App\Models\Price::factory(rand(1, 3))->create([
                'product_id' => $product->id,
            ]);
        });
    }

}
