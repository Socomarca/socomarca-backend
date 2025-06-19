<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Favorite>
 */
class FavoriteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return
        [
            'favorite_list_id' => \App\Models\FavoriteList::factory(),
            'product_id' => \App\Models\Product::factory()
                ->has(\App\Models\Price::factory()->count(2), 'prices'),
            'unit' => function (array $attributes) {
                $product = \App\Models\Product::find($attributes['product_id']);
                return $product->prices()->first()->unit;
            }
        ];
    }
}
