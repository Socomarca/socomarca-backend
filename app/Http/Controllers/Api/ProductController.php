<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShowProductRequest;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        return Product::paginate(20);
    }

    public function show(ShowProductRequest $showRequest, $id)
    {
        $showRequest->validated();

        if (!DB::table('products')->where('id', $id)->exists())
        {
            return response()->json(
            [
                'message' => 'Product not found.',
            ], 404);
        }

        $resources = Product::where('id', $id)->get();

        return response()->json(['resources' => $resources]);
    }

    public function byCategory(Request $request, $categoryId)
    {
        $products = Product::where('category_id', $categoryId)->paginate(20);
        return response()->json($products);
    }
}
