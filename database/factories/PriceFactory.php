<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Price>
 */
class PriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'product_id' => 'product_id',
            'price_list_id' => 'price_list_id',
            //'currency' => 'USD',
            'unit' => fake()->numberBetween(1, 10),
            'price' => fake()->randomFloat(2, 1000, 100000),
            'valid_from' => now()->subDays(rand(0, 30)),
            'valid_to' => null,
            'is_active' => true,
        ];
    }
}
