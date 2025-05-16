<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\ValidateRut;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class AuthRequest extends FormRequest
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
            'rut' => ['required', new ValidateRut()],
            'password' => [
                'required',
                'string',
                function($attribute, $value, $fail) {
                    $rut = $this->input('rut');
                    $user = User::where('rut', $rut)->first();
                    if (!$user) {
                        throw new HttpResponseException(
                            response()->json(['message' => 'Unauthorized'], 401)
                        );
                    }
                    if (!Hash::check($value, $user->password)) {
                        throw new HttpResponseException(
                            response()->json(['message' => 'Unauthorized'], 401)
                        );
                    }
                    if (!$user->is_active) {
                        throw new HttpResponseException(
                            response()->json(['message' => 'Unauthorized'], 403)
                        );
                    }
                    // inyectar usuario validado en la request
                    $this->merge(['auth_user' => $user]);
                }
            ],
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
            'rut.required' => 'El RUT es obligatorio',
            'password.required' => 'La contraseña es obligatoria',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422));
    }
}
