<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class CreateFromCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'rut' => 'required|string|max:12',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'region_id' => 'required|exists:regions,id',
            'municipality_id' => 'required|exists:municipalities,id',
            'billing_address' => 'required|string|max:255',
            'billing_address_details' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'El ID del usuario es requerido',
            'user_id.exists' => 'El usuario no existe',
            'name.required' => 'El nombre es requerido',
            'rut.required' => 'El RUT es requerido',
            'email.required' => 'El email es requerido',
            'email.email' => 'El email debe ser válido',
            'phone.required' => 'El teléfono es requerido',
            'address.required' => 'La dirección es requerida',
            'region_id.required' => 'La región es requerida',
            'region_id.exists' => 'La región seleccionada no existe',
            'municipality_id.required' => 'La comuna es requerida',
            'municipality_id.exists' => 'La comuna seleccionada no existe',
            'billing_address.required' => 'La dirección de facturación es requerida',
        ];
    }
} 