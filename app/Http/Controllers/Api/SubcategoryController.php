<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subcategories\ShowRequest;
use App\Http\Resources\Subcategories\SubcategoryCollection;
use App\Models\Subcategory;

class SubcategoryController extends Controller
{
    public function index()
    {
        $subcategories = Subcategory::all();

        $data = new SubcategoryCollection($subcategories);

        return $data;
    }

    public function show($id)
    {
        if (!Subcategory::find($id))
        {
            return response()->json(
            [
                'message' => 'Subcategory not found.',
            ], 404);
        }

        $subcategories = Subcategory::where('id', $id)->get();

        $data = new SubcategoryCollection($subcategories);

        return response()->json($data[0]);
    }
}
