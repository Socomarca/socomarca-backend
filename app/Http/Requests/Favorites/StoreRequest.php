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
        return $this->checkFavoriteListsOwnership();
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
            'favorite_list_id' => 'required',
            'product_id' => 'required|exists:products,id',
            'unit' => [
                'required',
                'string',
                new \App\Rules\ProductHasUnit($this->input('product_id')),
            ],
        ];
    }

    public function checkFavoriteListsOwnership(): bool
    {
        if (!$this->has('favorite_list_id')) return false;

        $favoriteListId = $this->input('favorite_list_id');
        $userId = $this->user()->id;

        return \App\Models\FavoriteList::where('id', $favoriteListId)
            ->where('user_id', $userId)
            ->exists();
    }


}
