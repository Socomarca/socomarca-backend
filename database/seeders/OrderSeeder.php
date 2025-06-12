<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Price;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::pluck('id')->toArray();
        $products = \App\Models\Product::pluck('id')->toArray();

        // Meses de 2025 a poblar
        $months = [
            '2025-01', '2025-02', '2025-03', '2025-04', '2025-05', '2025-06'
        ];

        foreach ($months as $month) {
            // Crea 10 órdenes por mes (ajusta la cantidad si lo deseas)
            for ($i = 0; $i < 100; $i++) {
                $userId = fake()->randomElement($users);

                // Fecha aleatoria dentro del mes
                $date = fake()->dateTimeBetween("$month-01", "$month-28");

                $subtotal = 0;
                $amount = 0;

                $order = \App\Models\Order::create([
                    'user_id' => $userId,
                    'subtotal' => 0,
                    'amount' => 0,
                    'status' => fake()->randomElement([
                        'pending', 'processing', 'on_hold', 'completed', 'canceled', 'refunded', 'failed'
                    ]),
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                $itemsCount = rand(1, 5);
                $productIds = fake()->randomElements($products, $itemsCount);

                foreach ($productIds as $productId) {
                    $priceObj = \App\Models\Price::where('product_id', $productId)
                        ->where('is_active', true)
                        ->inRandomOrder()
                        ->first();

                    if (!$priceObj) continue;

                    $quantity = rand(1, 10);
                    $itemTotal = $priceObj->price * $quantity;
                    $subtotal += $itemTotal;

                    \App\Models\OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $productId,
                        'unit' => $priceObj->unit,
                        'quantity' => $quantity,
                        'price' => $priceObj->price,
                    ]);
                }

                $amount = $subtotal; // Puedes ajustar si tienes lógica de descuentos/impuestos

                $order->update([
                    'subtotal' => $subtotal,
                    'amount' => $amount,
                ]);
            }
        }
    }
}
