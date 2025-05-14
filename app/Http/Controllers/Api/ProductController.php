<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return Product::paginate(20);
    }

    public function show(Product $product)
    {
        return $product;
    }

    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'name' => 'required|string',
    //         'description' => 'nullable|string',
    //         'subcategory_id' => 'required|exists:subcategories,id',
    //         'brand_id' => 'required|exists:brands,id',
    //     ]);

    //     return Product::create($data);
    // }

    // public function update(Request $request, Product $product)
    // {
    //     $data = $request->validate([
    //         'name' => 'sometimes|string',
    //         'description' => 'nullable|string',
    //         //'slug' => 'sometimes|string|unique:products,slug,' . $product->id,
    //         'subcategory_id' => 'sometimes|exists:subcategories,id',
    //         'brand_id' => 'sometimes|exists:brands,id',
    //     ]);

    //     $product->update($data);

    //     return $product;
    // }

    // public function destroy(Product $product)
    // {
    //     $product->delete();
    //     return response()->noContent();
    // }
}
