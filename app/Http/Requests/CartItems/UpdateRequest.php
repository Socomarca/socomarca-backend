<?php

namespace App\Http\Requests\CartItems;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'id'            => 'bail|integer|exists:carts,id',
            'quantity'      => 'bail|required|integer|min:1|max:99',
            'price'         => 'bail|required|integer',
        ];
    }

    public function messages()
    {
        return
        [
            'id.integer' => 'The id field in params must be an integer.',
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
