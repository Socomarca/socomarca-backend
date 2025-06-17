<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\ShowRequest;
use App\Http\Resources\Categories\CategoryCollection;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $perPage = request()->input('per_page', 20);
        $categories = Category::withCount(['subcategories', 'products'])->paginate($perPage);

        $data = new CategoryCollection($categories);

        return $data;
    }

    public function show($id)
    {
        if (!Category::find($id))
        {
            return response()->json(
            [
                'message' => 'Category not found.',
            ], 404);
        }

        $categories = Category::where('id', $id)->get();

        $data = new CategoryCollection($categories);

        return response()->json($data[0]);
    }

    /**
     * Search categories by filters
     *
     * @param Request $request
     *
     * @return CategoryCollection
     */
    public function search(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $filters = $request->input('filters', []);
        
        $result = Category::select("categories.*")
            ->withCount(['subcategories', 'products'])
            ->filter($filters)
            ->paginate($perPage);

        $data = new CategoryCollection($result);

        return $data;
    }
}
