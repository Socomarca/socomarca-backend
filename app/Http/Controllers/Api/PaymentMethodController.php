<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethods\IndexRequest;
use App\Http\Requests\PaymentMethods\UpdateRequest;
use App\Http\Resources\PaymentMethods\PaymentMethodCollection;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        // $data = $indexRequest->validated();
        $methods = PaymentMethod::where('active', true)->get();
        $data = new PaymentMethodCollection($methods);
        return $data;
    }

    public function update(UpdateRequest $updateRequest, $id)
    {
        $data = $updateRequest->validated();

        $paymentMethod = PaymentMethod::find($id);

        if (!$paymentMethod) {
            return response()->json([
                'message' => 'Payment method not found.',
            ], 404);
        }

        $paymentMethod->active = $data['active'];
        $paymentMethod->save();

        return response()->json(['message' => 'The payment method has been updated']);
    }
}
