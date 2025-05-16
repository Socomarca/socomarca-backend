<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreRequest extends FormRequest
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
        return
        [
            'name' => 'bail|required|string',
            'email' => 'bail|required|email|unique:users,email',
            'password' => ['bail', 'required', 'confirmed', Password::min(8)->letters()->numbers()],
            'phone' => 'bail|required|integer|digits:9',
            'rut' => 'bail|required|string|max:10|min:10',
            'business_name' => 'bail|required|string',
            'is_active' => 'bail|required|boolean',
        ];
    }
}
