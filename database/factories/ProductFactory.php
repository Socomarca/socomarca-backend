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
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'category_id' => \App\Models\Category::factory(),
            'subcategory_id' => \App\Models\Subcategory::factory(),
            'brand_id' => \App\Models\Brand::factory(),
            'sku' => $this->faker->unique()->bothify('SKU-####-???'),
            'status' => $this->faker->boolean(90),
        ];
    }
}
