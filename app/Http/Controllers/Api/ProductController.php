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

    public function search(Request $request)
    {
        $query = $request->input('search');

        if (!$query) {
            return response()->json(['message' => 'Search term is required'], 422);
        }

        $results = Product::search($query)->get();

        if ($request->filled('category_id')) {
            $results = $results->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('brand_id')) {
            $results = $results->where('brand_id', $request->input('brand_id'));
        }

        return response()->json($results);
    }

    // public function search(Request $request)
    // {
        
    //     $query = $request->input('query');
    //     $category = $request->input('category');
    //     $brand = $request->input('brand');

    //     $products = Product::search($query)
    //         ->get()
    //         ->when($category, fn($products) => $products->filter(fn($p) => $p->category_id == $category))
    //         ->when($brand, fn($products) => $products->filter(fn($p) => $p->brand_id == $brand));

    //     return response()->json($products);
    // }

    // public function searchWithLevenshtein(Request $request)
    // {
    //     $searchTerm = $request->input('search');

    //     if (!$searchTerm) {
    //         return response()->json(['message' => 'Search term is required'], 422);
    //     }

    //     $products = Product::all(); // Trae todos los productos (optimizar si son muchos)
        
    //     // Calcular distancia de Levenshtein para cada producto
    //     $results = $products->map(function ($product) use ($searchTerm) {
    //         $distance = levenshtein(strtolower($searchTerm), strtolower($product->name));
    //         return [
    //             'product' => $product,
    //             'distance' => $distance,
    //         ];
    //     });

    //     // Ordenar por similitud
    //     $sorted = $results->sortBy('distance')->values();

    //     // Solo devolver productos, puedes limitar a los 10 más parecidos
    //     return response()->json($sorted->pluck('product')->take(10));
    // }
}
