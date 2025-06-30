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
        
        $json = Storage::disk('local')->get('fake_seed_data/products.json');
        $categories = json_decode($json, true);

        foreach ($categories as $catData) {
            // Crea la categoría o la busca si ya existe
            $category = Category::firstOrCreate(
                ['name' => $catData['name']],
                [
                    'description' => $catData['name'],
                    'code' => fake()->regexify('[A-Z]{10}'),
                    'level' => fake()->numberBetween(1, 10),
                    'key' => fake()->regexify('[A-Z]{4}'),
                ]
            );

            foreach ($catData['subcategories'] as $subcatData) {
                // Crea la subcategoría o la busca si ya existe
                $subcategory = $category->subcategories()->firstOrCreate(
                    ['name' => $subcatData['name']],
                    [
                        'description' => $subcatData['name'],
                        'code' => fake()->regexify('[A-Z]{10}'),
                        'level' => fake()->numberBetween(1, 10),
                        'key' => fake()->regexify('[A-Z]{4}'),
                    ]
                );

                foreach ($subcatData['products'] as $productName) {
                    $product = Product::factory()
                        ->state([
                            'name' => $productName,
                            'category_id' => $category->id,
                            'subcategory_id' => $subcategory->id
                        ])
                        ->has(
                            Price::factory()
                                ->state(function () {
                                    return [
                                        'is_active' => true,
                                        'price' => random_int(5000, 70000),
                                        'unit' => fake()->randomElement(['un', 'kg', 'lt', 'gr']),
                                    ];
                                })
                                ->count(1)
                        )
                        ->create();
                }
            }
        }
        
        
    }

   
}
