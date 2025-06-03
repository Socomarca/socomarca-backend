<?php

namespace App\Http\Requests\Favorites;

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
        return
        [
            'favorite_list_id' => 'bail|required|integer|exists:favorites_list,id',
            'product_id' => 'bail|required|integer|exists:products,id',
        ];
    }
}
