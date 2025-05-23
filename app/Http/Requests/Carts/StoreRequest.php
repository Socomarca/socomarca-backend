<?php

namespace App\Http\Requests\Carts;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'user_id'       => 'bail|required|integer|exists:users,id',
            'product_id'    => 'bail|required|integer|exists:products,id',
            'quantity'      => 'bail|required|integer|min:1|max:99',
            'price'         => 'bail|required|integer',
        ];
    }
}
