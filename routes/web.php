<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebpayController;

Route::get('/', function () {
    return view('welcome');
});