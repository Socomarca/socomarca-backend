<?php

namespace App\Http\Requests\Faq;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasAnyRole(['superadmin', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question' => 'sometimes|required|string|min:10|max:1000',
            'answer' => 'sometimes|required|string|min:10|max:5000',
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
            'question.required' => 'La pregunta es obligatoria.',
            'question.string' => 'La pregunta debe ser texto.',
            'question.min' => 'La pregunta debe tener al menos 10 caracteres.',
            'question.max' => 'La pregunta no puede tener más de 1000 caracteres.',
            'answer.required' => 'La respuesta es obligatoria.',
            'answer.string' => 'La respuesta debe ser texto.',
            'answer.min' => 'La respuesta debe tener al menos 10 caracteres.',
            'answer.max' => 'La respuesta no puede tener más de 5000 caracteres.',
        ];
    }
}
