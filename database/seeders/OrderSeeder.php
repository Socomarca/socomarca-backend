<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los productos disponibles
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->error('No hay productos disponibles para crear órdenes.');
            return;
        }

        // Crear 50 órdenes
        Order::factory(150)->create()->each(function ($order) use ($products) {
            // Para cada orden, crear entre 1 y 5 items
            $numItems = rand(1, 5);
            
            for ($i = 0; $i < $numItems; $i++) {
                $product = $products->random();
                $quantity = rand(1, 10);
                $price = $product->prices->where('is_active', true)->first()->price ?? rand(1000, 10000);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'unit' => 'unidad',
                    'quantity' => $quantity,
                    'price' => $price
                ]);
            }

            // Actualizar el subtotal y amount de la orden
            $subtotal = $order->orderDetails->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            $order->update([
                'subtotal' => $subtotal,
                'amount' => $subtotal
            ]);
        });
    }
}
