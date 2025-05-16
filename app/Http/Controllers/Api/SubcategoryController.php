<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubcategoryIdRequest;
use Illuminate\Http\Request;
use App\Models\Subcategory;

class SubcategoryController extends Controller
{
    public function index()
    {
        return Subcategory::all();
    }

    public function show(SubcategoryIdRequest $request)
    {
        $subcategory = Subcategory::find($request->id);
        return response()->json($subcategory);
    }
}
