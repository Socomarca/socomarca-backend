<?php

namespace App\Http\Requests\Faq;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Permitir a todos buscar FAQs
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => 'nullable|string|min:2|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'filters' => 'nullable|array',
            'filters.*.field' => 'nullable|string|in:question,answer',
            'filters.*.operator' => 'nullable|string|in:=,!=,LIKE,ILIKE,NOT LIKE,fulltext',
            'filters.*.value' => 'nullable|string|max:255',
            'filters.*.sort' => 'nullable|string|in:ASC,DESC',
        ];
    }
}
