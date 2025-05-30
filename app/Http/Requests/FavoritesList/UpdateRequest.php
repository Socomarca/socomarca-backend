<?php

namespace App\Http\Requests\FavoritesList;

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
        return
        [
            'id' => 'bail|integer|exists:favorites_list,id',
            'name' => 'bail|required|string',
            'user_id' => 'bail|required|integer|exists:users,id',
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
