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

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'search.min' => 'El término de búsqueda debe tener al menos 2 caracteres.',
            'search.max' => 'El término de búsqueda no puede tener más de 255 caracteres.',
            'per_page.integer' => 'Los elementos por página debe ser un número entero.',
            'per_page.min' => 'Debe mostrar al menos 1 elemento por página.',
            'per_page.max' => 'No se pueden mostrar más de 100 elementos por página.',
            'filters.array' => 'Los filtros deben ser un array.',
        ];
    }
}
