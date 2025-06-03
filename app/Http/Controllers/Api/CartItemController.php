<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartItems\DestroyRequest;
use App\Http\Requests\CartItems\IndexRequest;
use App\Http\Requests\CartItems\ShowRequest;
use App\Http\Requests\CartItems\StoreRequest;
use App\Http\Requests\CartItems\UpdateRequest;
use App\Http\Resources\CartItems\CartItemCollection;
use App\Models\CartItem;
use App\Helpers\TotalHelper;
use App\Models\Price;
use Illuminate\Support\Facades\Auth;

class CartItemController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->id;

        $carts = CartItem::where('user_id', $userId)->get();

        return new CartItemCollection($carts);

       
    }

    public function store(StoreRequest $storeRequest)
    {
        $data = $storeRequest->validated();
        
        // Busca el precio activo del producto
        $price = Price::where('product_id', $data['product_id'])
            ->where('unit', $data['unit'])
            ->where('is_active', true)
            ->first();
        
        if (!$price) {
            return response()->json(['message' => 'No active price found for this product.'], 404);
        }
        
        // Busca si ya existe el item en el carrito para ese usuario, producto y unidad
        $cart = CartItem::where('user_id', Auth::user()->id)
            ->where('product_id', $data['product_id'])
            ->where('unit', $data['unit'])
            ->first();

        if ($cart) {
            // Si existe, suma la cantidad
            $cart->quantity += $data['quantity'];
            
            $cart->save();
            return response()->json(['message' => 'The product quantity in the cart has been updated'], 200);
        } else {
            // Si no existe, crea uno nuevo
            $cart = new CartItem;
            $cart->user_id = Auth::user()->id;
            $cart->product_id = $data['product_id'];
            $cart->quantity = $data['quantity'];
            $cart->unit = $data['unit'];
            
            $cart->save();
            return response()->json(['message' => 'The product in the cart has been added'], 201);
        }
    }

    public function update(UpdateRequest $updateRequest, $id)
    {
        $data = $updateRequest->validated();

        $cart = CartItem::where('id', $id)
        ->where('user_id', Auth::user()->id)
        ->first();

        if (!$cart)
        {
            return response()->json(
            [
                'message' => 'Product not found.',
            ], 404);
        }

        

         // Busca el precio activo del producto
        $price = Price::where('product_id', $cart->product_id)
            ->where('is_active', true)
            ->first();
        
        if (!$price) {
            return response()->json(['message' => 'No active price found for this product.'], 404);
        }
        
        

        $cart->quantity = $data['quantity'];
        $cart->unit = $price->unit;
        
        $cart->save();
        

        return response()->json(['message' => 'The selected product has been updated']);

    }

    public function destroy(DestroyRequest $destroyRequest, $id)
    {
        $destroyRequest->validated();

        $cart = CartItem::where('id', $id)
        ->where('user_id', Auth::user()->id)
        ->first();

        if (!$cart)
        {
            return response()->json(
            [
                'message' => 'Product not found.',
            ], 404);
        }

        $cart->delete();

        return response()->json(['message' => 'The selected product has been deleted']);
    }

    /*public function total($carrito)
    {
        // $userId = 1;
        // $carrito = Cart::where('user_id', $userId)->get();
        $totalProductos = [];
        $totalCarrito = 0;

        foreach ($carrito as $key => $producto) {
            $total = $producto['quantity'] * $producto['price'];
            array_push($totalProductos, $total);
        }

        foreach ($totalProductos as $value) {
            $totalCarrito += $value;
        }

        //dd($totalCarrito);
        return $totalCarrito;
    }*/
}
