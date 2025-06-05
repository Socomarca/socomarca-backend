<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Region>
 */
class RegionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         $code = str_pad($this->faker->numberBetween(1, 16), 2, '0', STR_PAD_LEFT);

        return [
            'name' => $this->faker->state(),
            'code' => $code,
        ];
    }
}
