<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\ShowRequest;
use App\Http\Resources\Categories\CategoryCollection;
use App\Http\Resources\Categories\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $data = Category::all();

        return new CategoryCollection($data);
    }

    public function show(ShowRequest $showRequest, $id)
    {
        $showRequest->validated();
        $category = Category::find($id);

        if (!$category)
        {
            return response()->json(
            [
                'message' => 'Category not found.',
            ], 404);
        }

        return new CategoryResource($category);
    }
}
