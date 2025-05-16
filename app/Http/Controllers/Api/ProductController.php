<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductIdRequest;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return Product::paginate(20);
    }

    public function show(ProductIdRequest $request)
    {
         $product = Product::find($request->id);
        return response()->json($product);

    }

    public function byCategory(Request $request, $categoryId)
{
    $products = Product::where('category_id', $categoryId)->paginate(20);
    return response()->json($products);
}

}
