<?php

namespace App\Http\Requests\Addresses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'address_line1' => 'bail|required|string',
            'address_line2' => 'bail|required|string',
            'postal_code' => 'bail|required|integer',
            'is_default' => 'bail|required|boolean',
            'type' => ['bail', 'required', Rule::in(['billing', 'shipping'])],
            'phone' => 'bail|required|integer|digits:9',
            'contact_name' => 'bail|required|string',
            'municipality_id' => 'bail|required|integer|exists:municipalities,id',
        ];
    }
}
