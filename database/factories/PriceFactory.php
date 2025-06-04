<?php

namespace Database\Factories;

use App\Models\Price;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Price>
 */
class PriceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Price::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first();

        if (!$product) {
            $product = Product::factory()->create();
        }

        return [
            'price' => $this->faker->numberBetween(1000, 55000),
            'product_id' => $product->id,
            'price_list_id' => fake()->regexify('[A-Z]{10}'),
            'unit' => $this->faker->randomElement(['kg', 'gr', 'un']),
            'valid_from' => now()->subDays(rand(0, 30)),
            'valid_to' => null,
            'is_active' => true,
            'stock' => rand(100,200)
        ];
    }
}
