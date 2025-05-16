<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAddressRequest extends FormRequest
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
            'id' => 'bail|integer',
            'address_line1' => 'bail|required|string',
            'address_line2' => 'bail|required|string',
            'postal_code' => 'bail|required|integer',
            'is_default' => 'bail|required|boolean',
            'type' => ['bail', 'required', Rule::in(['billing', 'shipping'])],
            'phone' => 'bail|required|integer|digits:9',
            'contact_name' => 'bail|required|string',
            'user_id' => 'bail|required|integer|exists:users,id',
            'municipality_id' => 'bail|required|integer|exists:municipalities,id',
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
