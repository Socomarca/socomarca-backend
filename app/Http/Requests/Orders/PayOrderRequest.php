<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class PayOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'user_id' => 'required|exists:users,id|integer'
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'El ID de la orden es requerido',
            'order_id.exists' => 'La orden no existe',
            'user_id.required' => 'El ID del usuario es requerido',
            'user_id.exists' => 'El usuario no existe',
            'user_id.integer' => 'El ID del usuario debe ser un número entero'
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('user_id')) {
            $this->merge([
                'user_id' => (int) $this->user_id
            ]);
        }
    }
} 