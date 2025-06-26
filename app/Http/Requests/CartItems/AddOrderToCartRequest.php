<?php

namespace App\Http\Requests\CartItems;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AddOrderToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!$this->order_id) {
            return true; // Dejar que las validaciones manejen el campo faltante
        }
        
        $order = Order::find($this->order_id);
        if (!$order) {
            return true; // Dejar que las validaciones manejen order_id inexistente
        }
        
        return $order->user_id === Auth::id();
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|integer|exists:orders,id'
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'El ID de la orden es requerido',
            'order_id.integer' => 'El ID de la orden debe ser un número entero',
            'order_id.exists' => 'La orden especificada no existe'
        ];
    }
} 