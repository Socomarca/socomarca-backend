<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\ShowRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $data = Category::all();

        return response()->json(['data' => $data]);
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

        $data = Category::where('id', $id)->get();

        return $data[0];
    }
}
