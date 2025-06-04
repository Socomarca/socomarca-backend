<?php

namespace App\Http\Requests\CartItems;

use App\Rules\ProductMustHavePrice;
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
            'product_id'    => [
                'bail',
                'required',
                'exists:products,id',
                new ProductMustHavePrice($this->input('unit', ''))
            ],
            'quantity'      => 'bail|required|integer|min:1|max:99',
            'unit'          => 'required|string|max:10',
        ];
    }
}
