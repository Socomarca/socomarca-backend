<?php

namespace Database\Factories;

use App\Models\Price;
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
        return [
            'price' => $this->faker->randomFloat(2, 1000, 100000),
            'product_id' => \App\Models\Product::factory(),
            'price_list_id' => $this->faker->word(),
            'unit' => $this->faker->randomElement(['kg', 'gr', 'un']),
            'valid_from' => now()->subDays(rand(0, 30)),
            'valid_to' => null,
            'is_active' => true,
        ];
    }
}
