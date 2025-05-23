<?php

namespace App\Http\Requests\Favorites;

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
            'user_id' => 'bail|required|integer|exists:lists_favorites,user_id',
            'list_favorite_id' => 'bail|required|integer|exists:favorites,list_favorite_id',
        ];
    }
}
