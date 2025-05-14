<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function show(Category $category)
    {
        return $category;
    }

    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'name' => 'required|string',
    //         'description' => 'nullable|string',
    //         //'slug' => 'required|string|unique:categories,slug',
    //     ]);

    //     return Category::create($data);
    // }

    // public function update(Request $request, Category $category)
    // {
    //     $data = $request->validate([
    //         'name' => 'sometimes|string',
    //         'description' => 'nullable|string',
    //         //'slug' => 'sometimes|string|unique:categories,slug,' . $category->id,
    //     ]);

    //     $category->update($data);

    //     return $category;
    // }

    // public function destroy(Category $category)
    // {
    //     $category->delete();

    //     return response()->noContent();
    // }
}
