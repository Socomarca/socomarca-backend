<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Municipality>
 */
class MunicipalityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $regionId = \App\Models\Region::inRandomOrder()->value('id');

        // Genera un código numérico de 5 dígitos como string (ej: '13101')
        $code = (string) $this->faker->numberBetween(10000, 99999);

        return [
            'name' => $this->faker->city,
            'code' => $this->faker->postcode,
            'region_id' => \App\Models\Region::factory(),
        ];
    }
}
