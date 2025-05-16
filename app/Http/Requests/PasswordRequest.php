<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use App\Rules\ValidateRut;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PasswordRequest extends FormRequest
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
            'rut' => ['required', 'string', new ValidateRut(),
            function($attribute, $value, $fail) {
                $rut = $this->input('rut');
                $user = User::where('rut', $rut)->first();
                if (!$user) {
                    throw new HttpResponseException(
                        response()->json(['message' => 'Unauthorized'], 401)
                    );
                }
                // inyectar usuario validado en la request
                $this->merge(['user' => $user]);
            },
            ],
        ];
        
    }
      /**
     * Mensajes personalizados para errores de validación.
     */
    public function messages(): array
    {
        return [
            'rut.required' => 'El RUT es obligatorio.',
        ];
    }
  

    /**
     * Manejar un intento fallido de validación.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Error de validación',
            'errors' => $validator->errors(),
        ], 422));
    }
}
