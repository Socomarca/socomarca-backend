<?php

namespace App\Http\Requests\FavoritesList;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
            'user_id' => 'bail|required|integer|exists:favorites_list,user_id',
        ];
    }

    public function messages()
    {
        return
        [
            'user_id.required' => 'The user_id field in query params is required.',
            'user_id.integer' => 'The user_id field in query params must be an integer.',
            'user_id.exists' => 'The selected user in query params is invalid.',
        ];
    }
}
