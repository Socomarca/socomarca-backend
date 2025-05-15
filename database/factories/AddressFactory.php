<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $municipalityId = DB::table('municipalities')->inRandomOrder()->first()->id;

        return
        [
            'user_id' => User::factory(),
            'address_line1' => fake()->streetAddress(),
            'address_line2' => fake()->secondaryAddress(),
            'municipality_id' => $municipalityId,
            'postal_code' => fake()->numberBetween(2000000, 2999999), // 2200055
            'is_default' => fake()->boolean(),
            'type' => fake()->randomElement(['billing', 'shipping']),
            'phone' => fake()->numberBetween(777777777, 999999999),
            'contact_name' => fake()->name(),
        ];
    }
}
