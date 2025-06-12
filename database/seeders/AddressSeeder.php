<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Address;
use App\Models\User;
use App\Models\Municipality;
use Faker\Factory as Faker;

class AddressSeeder extends Seeder
{
    public function run(): void
    {

        $users = User::all();
        foreach ($users as $user) {
            for ($i = 0; $i < 3; $i++) {
                Address::create([
                    'user_id' => $user->id,
                    'address_line1' => fake()->address,
                    'address_line2' => fake()->secondaryAddress,
                    'municipality_id' => Municipality::inRandomOrder()->first()->id,
                    'postal_code' => fake()->numberBetween(2000000, 2999999),
                    'is_default' => fake()->boolean,
                    'type' => fake()->randomElement(['billing', 'shipping']),
                    'phone' => fake()->numberBetween(777777777, 999999999),
                    'contact_name' => fake()->name,
                ]);
            }
        }
    }
}
