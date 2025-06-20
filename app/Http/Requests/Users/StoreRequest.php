<?php

namespace App\Http\Requests\Users;

use App\Rules\ValidateRut;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage-users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return
        [
            'name' => 'bail|required|string|max:255',
            'email' => 'bail|required|email|unique:users,email|max:255',
            'password' => ['bail', 'required', 'confirmed', Password::min(8)->letters()],
            'phone' => 'bail|required|string|max:15',
            'rut' => ['bail', 'required', 'string', 'max:12', 'unique:users,rut', new ValidateRut],
            'business_name' => 'bail|required|string|max:255',
            'is_active' => 'bail|required|boolean',
            'roles' => 'bail|sometimes|array',
            'roles.*' => 'bail|string|exists:roles,name',
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
            'name.required' => 'El nombre es requerido.',
            'email.required' => 'El email es requerido.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es requerida.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'phone.required' => 'El teléfono es requerido.',
            'rut.required' => 'El RUT es requerido.',
            'rut.unique' => 'Este RUT ya está registrado.',
            'business_name.required' => 'El nombre de la empresa es requerido.',
            'is_active.required' => 'El estado es requerido.',
            'is_active.boolean' => 'El estado debe ser verdadero o falso.',
            'roles.array' => 'Los roles deben ser un arreglo.',
            'roles.*.exists' => 'Uno o más roles no existen.',
        ];
    }
}
