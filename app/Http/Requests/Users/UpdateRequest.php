<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'id' => 'bail|integer|exists:users,id',
            'name' => 'bail|required|string',
            'email' => ['bail', 'required', 'email', Rule::unique('users')->ignore($this->route('user'))],
            'phone' => 'bail|required|integer|digits:9',
            'rut' => 'bail|required|string|max:10|min:10',
            'business_name' => 'bail|required|string',
            'is_active' => 'bail|required|boolean',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(
        [
            'id' => $this->route('id'),
        ]);
    }
}
