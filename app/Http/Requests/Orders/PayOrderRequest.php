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
            'address_id' => [
                'required',
                'exists:addresses,id',
                function ($attribute, $value, $fail) {
                    $address = \App\Models\Address::where('id', $value)
                        ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                        ->first();
                    
                    if (!$address) {
                        $fail('La dirección no pertenece al usuario actual.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.required' => 'El debe seleccionar una dirección de envío.',
            'address_id.exists' => 'La dirección seleccionada no es válida.',
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