<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ShowRequest;
use App\Http\Resources\Products\ProductCollection;
use App\Http\Resources\Products\ProductResource;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
         
        $validator = Validator::make($request->all(), [
            'filters' => 'required|array',
            'filters.price' => 'required|array',
            'filters.price.min' => 'required|numeric|min:0',
            'filters.price.max' => 'required|numeric|gt:filters.price.min',
            'filters.price.unit' => 'sometimes|string|max:10',
            'filters.category_id' => 'sometimes|integer|exists:categories,id',
            'filters.subcategory_id' => 'sometimes|integer|exists:subcategories,id', // <-- Nuevo
            'filters.brand_id' => 'sometimes|integer|exists:brands,id',             // <-- Nuevo
            'filters.name' => 'sometimes|string|max:255',    
            'filters.is_favorite' => 'sometimes|boolean',
            
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'invalid data search.', 'errors' => $validator->errors()], 422);
        }

        $validatedFilters = $validator->validated()['filters'];
        $perPage = $request->input('per_page', 20);

        
        $result = Product::select("products.*")
            ->filter($validatedFilters)
            ->paginate($perPage);

        
        $data = new ProductCollection($result)->additional([
            'filters' => [
                'min_price' => $validatedFilters['price']['min'],
                'max_price' => $validatedFilters['price']['max'],
            ]
        ]);

        return $data;
    }

    /**
     * Get the products with the lowest and highest price.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPriceExtremes()
    {
        // Encuentra el registro de precio más bajo (activo)
        $minPriceRecord = Price::select('price')->where('is_active', true)->orderBy('price', 'asc')->first();

        // Encuentra el registro de precio más alto (activo)
        $maxPriceRecord = Price::select('price')->where('is_active', true)->orderBy('price', 'desc')->first();
       

        return response()->json([
            'lowest_price_product' => $minPriceRecord ? (int) $minPriceRecord->price : null,
            'highest_price_product' => $maxPriceRecord ? (int) $maxPriceRecord->price : null,
        ]);
    }
}