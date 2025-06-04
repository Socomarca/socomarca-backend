<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ShowRequest;
use App\Http\Resources\Products\ProductCollection;
use App\Http\Resources\Products\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $products = Product::with(['prices' => function($q) {
            $q->where('is_active', true);
        }])->paginate($perPage);
        $data = new ProductCollection($products);
        return $data;
    }

    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(
                [
                    'message' => 'Product not found.',
                ],
                404
            );
        }

        $data = new ProductResource($product);

        return $data;
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
        $filters = $request->input('filters', []);
        $result = Product::select("products.*")
            ->filter($filters)
            ->paginate($perPage);

        $priceFilter = collect($filters)->firstWhere('field', 'price');
        $minPrice = $priceFilter['min'] ?? null;
        $maxPrice = $priceFilter['max'] ?? null;
        $unit     = $priceFilter['unit'] ?? null;

        $data = new ProductCollection($result)->additional([
            'filters' => [
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'unit' => $unit,
            ]
        ]);

        return $data;
    }
}
