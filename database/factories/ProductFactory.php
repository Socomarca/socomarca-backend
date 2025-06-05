<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
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
            'category_id' => Category::factory(),
            'subcategory_id' => function(array $attributes) {
                $category = Category::find($attributes['category_id']);
                return Subcategory::factory([
                        'category_id' => $category->id
                    ])->create();
            },
            'brand_id' => Brand::factory(),
            'sku' => $this->generateSku(),
            'status' => $this->faker->boolean(90),
            'image' => "/assets/global/logo_plant.png"
        ];
    }

    private function generateSku(): string
    {
        $string1 = fake()->numberBetween(10000, 99999);
        $string2 = fake()->numberBetween(10000, 99999);
        return "SKU-{$string1}-{$string2}";
    }
}
