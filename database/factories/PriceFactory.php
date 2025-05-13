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
            'product_id' => \App\Models\Product::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            //'currency' => 'USD',
            'valid_from' => now()->subDays(rand(0, 30)),
            'valid_to' => null,
            'is_active' => true,
        ];
    }
}
