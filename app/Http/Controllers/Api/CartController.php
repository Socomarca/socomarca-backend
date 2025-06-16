<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItems\CartItemCollection;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Muestra los ítems del carrito de un usuario
     *
     * @return CartItemCollection
     */
    public function index()
    {
        $userId = Auth::user()->id;
        $items = CartItem::where('user_id', $userId)->orderBy('id','ASC')->get();
        return new CartItemCollection($items);
    }
}
