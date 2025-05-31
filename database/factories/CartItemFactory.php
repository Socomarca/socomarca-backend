<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user_id = \App\Models\User::inRandomOrder()->pluck('id')->first();
        $product_id = \App\Models\Product::inRandomOrder()->pluck('id')->first();

        return [
            'user_id' => $user_id,
            'product_id' => $product_id,
            'quantity' => fake()->numberBetween(1, 10),
        ];
    }
}

