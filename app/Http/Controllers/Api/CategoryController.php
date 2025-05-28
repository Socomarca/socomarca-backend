<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\ShowRequest;
use App\Http\Resources\Categories\CategoryCollection;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        $data = new CategoryCollection($categories);

        return $data;
    }

    public function show(ShowRequest $showRequest, $id)
    {
        $showRequest->validated();

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
}
