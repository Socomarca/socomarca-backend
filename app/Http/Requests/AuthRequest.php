<?php

namespace App\Http\Requests;

use App\Rules\ValidateRut;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Obtener el controlador y método que está siendo ejecutado
        $routeAction = $this->route()->getAction();
        $controller = class_basename($routeAction['controller'] ?? 'Controller@method');
        list($controllerName, $method) = explode('@', $controller);
        
        // Los métodos que no requieren autenticación
        $publicMethods = [
            'AuthController@login',
            'PasswordResetController@forgotPassword',
            // 'PasswordResetController@verifyTokenByRut',
            // 'PasswordResetController@resetPasswordByRut'
        ];
        
        // Si es un método público, permitir acceso
        if (in_array("$controllerName@$method", $publicMethods)) {
            return true;
        }
        
        // Para otros métodos, verificar autenticación
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Obtener el controlador y método que está siendo ejecutado
        $routeAction = $this->route()->getAction();
        $controller = class_basename($routeAction['controller'] ?? 'Controller@method');
        list($controllerName, $method) = explode('@', $controller);
        
        // Reglas específicas para cada método
        $rules = [
            'AuthController@login' => [
                'rut' => ['required', new ValidateRut()],
                'password' => 'required|string',
            ],
            'PasswordResetController@forgotPassword' => [
                'rut' => ['required', 'exists:users,rut', new ValidateRut()],
            ],
            // 'PasswordResetController@changePassword' => [
            //     'current_password' => 'required|string',
            //     'password' => 'required|string|min:8|confirmed|different:current_password',
            // ],
            // 'PasswordResetController@verifyTokenByRut' => [
            //     'rut' => ['required', 'exists:users,rut', new ValidateRut()],
            //     'token' => 'required|string',
            // ],
            // 'PasswordResetController@resetPasswordByRut' => [
            //     'rut' => ['required', 'exists:users,rut', new ValidateRut()],
            //     'token' => 'required|string',
            //     'password' => 'required|string|min:8|confirmed',
            // ],
            // 'AuthController@me' => [],
            // 'PasswordResetController@checkPasswordStatus' => [],
        ];
        
        // Devolver las reglas para el método actual o un array vacío si no hay reglas específicas
        return $rules["$controllerName@$method"] ?? [];
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
            'rut.exists' => 'No se encontró un usuario con este RUT',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'La confirmación de contraseña no coincide',
            'token.required' => 'El token es obligatorio',
            'current_password.required' => 'La contraseña actual es obligatoria',
            'password.different' => 'La nueva contraseña debe ser diferente a la actual',
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
            'status' => false,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422));
    }
}
