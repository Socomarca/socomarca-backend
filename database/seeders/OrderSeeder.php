<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Price;
use App\Models\Product;
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
         $users = User::pluck('id')->toArray();
        $products = Product::pluck('id')->toArray();

        for ($i = 0; $i < 20; $i++) {
            $userId = fake()->randomElement($users);

            $subtotal = 0;
            $amount = 0;

            // Crea la orden sin subtotal ni amount (se actualiza después)
            $order = Order::create([
                'user_id' => $userId,
                'subtotal' => 0,
                'amount' => 0,
                'status' => fake()->randomElement([
                    'pending',    // Orden creada, pendiente de pago
                    'processing', // Orden en proceso de pago
                    'on_hold',    // Orden en espera
                    'completed',  // Pago exitoso
                    'canceled',   // Orden cancelada
                    'refunded',   // Reembolso realizado
                    'failed',     // Pago fallido
                ]),
            ]);

            // Cada orden tendrá entre 1 y 5 productos
            $itemsCount = rand(1, 5);
            $productIds = fake()->randomElements($products, $itemsCount);

            foreach ($productIds as $productId) {
                $priceObj = Price::where('product_id', $productId)
                    ->where('is_active', true)
                    ->inRandomOrder()
                    ->first();

                if (!$priceObj) continue;

                $quantity = rand(1, 10);
                $itemTotal = $priceObj->price * $quantity;
                $subtotal += $itemTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'unit' => $priceObj->unit,
                    'quantity' => $quantity,
                    'price' => $priceObj->price,
                ]);
            }

            // Puedes agregar lógica de descuentos, impuestos, etc. para calcular amount
            $amount = $subtotal; // Aquí amount = subtotal, ajusta si necesitas lógica extra

            $order->update([
                'subtotal' => $subtotal,
                'amount' => $amount,
            ]);
        }
    }
}
