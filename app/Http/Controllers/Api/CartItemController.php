<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Carts\DestroyRequest;
use App\Http\Requests\Carts\IndexRequest;
use App\Http\Requests\Carts\ShowRequest;
use App\Http\Requests\Carts\StoreRequest;
use App\Http\Requests\Carts\UpdateRequest;
use App\Http\Resources\Carts\CartCollection;
use App\Models\CartItem;
use App\Helpers\MyHelper;

class CartItemController extends Controller
{
    public function index(IndexRequest $indexRequest)
    {
        $data = $indexRequest->validated();

        $userId = $indexRequest->user_id;

        $carts = CartItem::where('user_id', $userId)->get();
        $total = MyHelper::total($carts);

        $data = new CartCollection($carts);

        return response()->json([
            'data' => $data,
            'total' => $total,
        ]);
        //return $data;
    }

    public function store(StoreRequest $storeRequest)
    {
        $data = $storeRequest->validated();

        $cart = new CartItem;

        $cart->user_id = $data['user_id'];
        $cart->product_id = $data['product_id'];
        $cart->quantity = $data['quantity'];
        $cart->price = $data['price'];

        $cart->save();
        //$total = $this->total($data['user_id']);

        return response()->json(['message' => 'The product in the cart has been added'], 201);
    }


    public function update(UpdateRequest $updateRequest, $id)
    {
        $data = $updateRequest->validated();

        $cart = CartItem::find($id);
        if (!$cart)
        {
            return response()->json(
            [
                'message' => 'Product not found.',
            ], 404);
        }
        $userId = $cart->user_id;

        $cart->quantity = $data['quantity'];
        $cart->price = $data['price'];
        $cart->save();
        //$total = $this->total($userId);

        return response()->json(['message' => 'The selected product has been updated']);

    }

    public function destroy(DestroyRequest $destroyRequest, $id)
    {
        $destroyRequest->validated();

        $cart = CartItem::find($id);
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
