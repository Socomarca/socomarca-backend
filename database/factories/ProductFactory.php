<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $price = \App\Models\Price::inRandomOrder()->first();
        $brand = \App\Models\Brand::inRandomOrder()->first();
        $category = \App\Models\Category::inRandomOrder()->first();
        $subcategory = \App\Models\Subcategory::where('category_id', $category->id)
            ->inRandomOrder()
            ->first();
        $string1 = fake()->numberBetween(10000, 99999);
        $string2 = fake()->numberBetween(10000, 99999);
        $sku = "SKU-{$string1}-{$string2}";

        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price_id' => $price->id,
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'brand_id' => $brand->id,
            'sku' => $sku,
            'status' => $this->faker->boolean(90),
        ];
    }
}
