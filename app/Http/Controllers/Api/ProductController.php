<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ShowRequest;
use App\Http\Resources\Products\ProductCollection;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(20);

        $data = new ProductCollection($products);

        return $data;
    }

    public function show(ShowRequest $showRequest, $id)
    {
        $showRequest->validated();

        if (!Product::find($id))
        {
            return response()->json(
            [
                'message' => 'Product not found.',
            ], 404);
        }

        $product = Product::where('id', $id)->get();

        $data = new ProductCollection($product);

        return response()->json($data[0]);
    }

    public function byCategory(Request $request, $categoryId)
    {
        $products = Product::where('category_id', $categoryId)->paginate(20);
        return response()->json($products);
    }

    /**
     * Search products by filters
     *
     * @param Request $request
     *
     * @return ProductCollection
     */
    public function search(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $filters = $request->input('filters');
        $result = Product::select("products.*")
            ->filter($filters)
            ->paginate($perPage);

        $data = new ProductCollection($result);

        return $data;
    }

}
