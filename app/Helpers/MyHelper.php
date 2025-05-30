<?php

namespace App\Helpers;

class MyHelper
{
    public static function total($carrito)
    {
        $totalProductos = [];
        $totalCarrito = 0;

        foreach ($carrito as $key => $producto) {
            $total = $producto['quantity'] * $producto['price'];
            array_push($totalProductos, $total);
        }

        foreach ($totalProductos as $value) {
            $totalCarrito += $value;
        }

        return $totalCarrito;
    }
}