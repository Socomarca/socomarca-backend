<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShowCategoryRequest;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function show(ShowCategoryRequest $showRequest, $id)
    {
        $showRequest->validated();

        if (!DB::table('categories')->where('id', $id)->exists())
        {
            return response()->json(
            [
                'message' => 'Category not found.',
            ], 404);
        }

        $resources = Category::where('id', $id)->get();

        return response()->json(['resources' => $resources]);
    }
}
