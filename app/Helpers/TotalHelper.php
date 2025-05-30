<?php

namespace App\Helpers;

use App\Models\Price;
use App\Models\Product;

class TotalHelper
{
    public static function totalCarrito($carrito)
    {

        $totalCarrito = 0;
        foreach ($carrito as $value) {

            $totalCarrito += $value->subtotal;
        }

        return $totalCarrito;
    }
}