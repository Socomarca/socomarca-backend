<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

/**
 * Categories, subcategories and products seeder
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $fakeCategories = $this->getFakeCategories();

        foreach ($fakeCategories as $fc) {
            $category = Category::create([
                'name' => $fc->name,
                'description' => $fc->description,
                'code' => fake()->regexify('[A-Z]{10}'),
                'level' => fake()->numberBetween(1, 10),
                'key' => fake()->regexify('[A-Z]{4}'),
            ]);

            foreach ($fc->subcategories as $sc) {
                $category->subcategories()->create([
                    'name' => $sc->name,
                    'description' => $sc->description,
                    'code' => fake()->regexify('[A-Z]{10}'),
                    'level' => fake()->numberBetween(1, 10),
                    'key' => fake()->regexify('[A-Z]{4}'),
                ]);

                Product::factory([
                    'category_id' => $category->id,
                    'subcategory_id' => $category->subcategories()->first()->id
                ])
                    ->has(
                        Price::factory([
                            'is_active' => true,
                            'price' => random_int(50000, 100000)
                        ])->count(2)
                    )
                    ->count(15)
                    ->create();
            }
        }
    }

    public function getFakeCategories()
    {
        $categoriesJsonFile = Storage::disk('local')->get('fake_seed_data/categories.json');
        $categories = json_decode($categoriesJsonFile);
        return $categories;
    }
}
