<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Price;

class PriceController extends Controller
{
    public function index()
    {
        return Price::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'amount' => 'required|numeric|min:0',
            //'currency' => 'required|string|size:3',
        ]);

        return Price::create($data);
    }

    public function show(Price $price)
    {
        return $price;
    }

    public function update(Request $request, Price $price)
    {
        $data = $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'amount' => 'sometimes|numeric|min:0',
            //'currency' => 'sometimes|string|size:3',
        ]);

        $price->update($data);

        return $price;
    }

    public function destroy(Price $price)
    {
        $price->delete();
        return response()->noContent();
    }
}
