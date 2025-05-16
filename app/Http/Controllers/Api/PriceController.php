<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Price;

class PriceController extends Controller
{
    public function index()
    {
        return Price::all();
    }

}
