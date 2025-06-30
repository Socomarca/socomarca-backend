<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'unit' => fake()->randomElement(['kg', 'g', 'l', 'ml', 'unidad']),
            'quantity' => fake()->numberBetween(1, 10),
            'price' => fake()->randomFloat(2, 10, 1000),
        ];
    }
} 