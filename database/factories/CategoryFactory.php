<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
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
            'name' => ucfirst($name),
            'description' => $this->faker->sentence(),
            'code' => $this->faker->unique()->numberBetween(10000, 99999),
            'level' => $this->faker->numberBetween(1, 3),
            'key' => $this->faker->unique()->word(),
            //'slug' => Str::slug($name),
        ];
    }
}
