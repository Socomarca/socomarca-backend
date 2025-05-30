<?php

namespace App\Helpers;

use App\Models\Price;
use App\Models\Product;

class TotalHelper
{
    // public static function total($carrito)
    // {
    //     $totalProductos = [];
    //     $totalCarrito = 0;

    //     foreach ($carrito as $key => $producto) {

    //         $productId = $producto['id'];
    //         $product = Product::where('id', $productId)->first();
    //         $priceObj = Price::where('id', $product->price_id)->where('is_active', 1)->first();
    //         $price = $priceObj->price;
            
    //         $total = $producto['quantity'] * $price;
    //         array_push($totalProductos, $total);
    //     }

    //     dd($totalProductos);
    //     foreach ($totalProductos as $value) {
    //         $totalCarrito += $value;
    //     }

    //     return $totalCarrito;
    // }

    public static function totalCarrito($carrito)
    {

        $totalCarrito = 0;
        foreach ($carrito as $value) {
            $totalCarrito += $value->subtotal;
        }

        return $totalCarrito;
    }
}