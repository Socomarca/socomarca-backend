<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CartItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los usuarios y productos disponibles
        $users = User::all();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->error('No hay usuarios o productos disponibles para crear items del carrito.');
            return;
        }

        // Crear entre 1 y 5 items por usuario
        foreach ($users as $user) {
            $numItems = rand(1, 5);
            
            for ($i = 0; $i < $numItems; $i++) {
                $product = $products->random();
                $quantity = rand(1, 10);

                CartItem::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit' => $product->prices->where('is_active', true)->first()->unit,
                ]);
            }
        }
    }
}
