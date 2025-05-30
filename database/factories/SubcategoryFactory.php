<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subcategory>
 */
class SubcategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = $this->faker->unique()->word();

        return [
            'category_id' => \App\Models\Category::factory(),
            'name' => ucfirst($name),
            'description' => $this->faker->sentence(),
            'code' => fake()->regexify('[A-Z]{10}'),
            'level' => fake()->numberBetween(1, 10),
            'key' => fake()->regexify('[A-Z]{4}'),
        ];
    }
}
