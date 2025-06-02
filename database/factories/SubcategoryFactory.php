<?php

namespace Database\Factories;

use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subcategory>
 */
class SubcategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subcategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'category_id' => Category::factory(),
            'code' => fake()->regexify('[A-Z]{10}'),
            'level' => fake()->numberBetween(1, 10),
            'key' => fake()->regexify('[A-Z]{4}'),
        ];
    }
}
