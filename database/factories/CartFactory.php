<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cart>
 */
class CartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user_id = User::inRandomOrder()->pluck('id')->first();
        $product_id = Product::inRandomOrder()->pluck('id')->first();

        return [
            'user_id' => $user_id,
            'product_id' => $product_id,
            'quantity' => fake()->numberBetween(1, 10),
            'price' => $this->faker->randomFloat(2, 1000, 100000),
        ];
    }
}
