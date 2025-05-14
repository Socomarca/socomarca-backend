<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subcategory;

class SubcategoryController extends Controller
{
    public function index()
    {
        return Subcategory::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            //'slug' => 'required|string|unique:subcategories,slug',
            'category_id' => 'required|exists:categories,id',
        ]);

        return Subcategory::create($data);
    }

    public function show(Subcategory $subcategory)
    {
        return $subcategory;
    }

    public function update(Request $request, Subcategory $subcategory)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            //'slug' => 'sometimes|string|unique:subcategories,slug,' . $subcategory->id,
            'category_id' => 'sometimes|exists:categories,id',
        ]);

        $subcategory->update($data);

        return $subcategory;
    }

    public function destroy(Subcategory $subcategory)
    {
        $subcategory->delete();
        return response()->noContent();
    }
}
