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

    public function search(Request $request) //Meilisearch
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

    // public function search(Request $request) //levenshtein
    // {
    //     $searchTerm = $request->input('search');

    //     if (!$searchTerm) {
    //         return response()->json(['message' => 'Search term is required'], 422);
    //     }

    //     $query = Product::query();

    //     if ($request->filled('category_id')) {
    //         $query->where('category_id', $request->input('category_id'));
    //     }

    //     if ($request->filled('brand_id')) {
    //         $query->where('brand_id', $request->input('brand_id'));
    //     }

    //     $products = $query->get();

    //     $results = $products->map(function ($product) use ($searchTerm) {
    //         $words = explode(' ', mb_strtolower($product->name));
    //         $minDistance = collect($words)->map(function ($word) use ($searchTerm) {
    //             return levenshtein($word, mb_strtolower($searchTerm));
    //         })->min();

    //         return [
    //             'product' => $product,
    //             'distance' => $minDistance,
    //         ];
    //     })->sortBy('distance')->values();

    //     return response()->json($results->pluck('product')->take(10));
    // }

    // public function search(Request $request) //Similar Text
    // {
    //     $searchTerm = $request->input('search');

    //     if (!$searchTerm) {
    //         return response()->json(['message' => 'Search term is required'], 422);
    //     }

    //     $query = Product::query();

    //     if ($request->filled('category_id')) {
    //         $query->where('category_id', $request->input('category_id'));
    //     }

    //     if ($request->filled('brand_id')) {
    //         $query->where('brand_id', $request->input('brand_id'));
    //     }

    //     $products = $query->get();

    //     // Calcular porcentaje de similitud para cada producto
    //     $results = $products->map(function ($product) use ($searchTerm) {
    //         $similarity = 0;

    //         // Comparamos con el nombre completo
    //         similar_text(mb_strtolower($searchTerm), mb_strtolower($product->name), $percent);

    //         return [
    //             'product' => $product,
    //             'similarity' => $percent,
    //         ];
    //     });

    //     // Ordenar de mayor a menor porcentaje de similitud
    //     $sorted = $results->sortByDesc('similarity')->values();

    //     // Devolver los 10 más similares
    //     return response()->json($sorted->pluck('product')->take(10));
    // }

}
