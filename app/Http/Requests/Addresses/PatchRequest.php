<?php
namespace App\Http\Requests\Addresses;

use Illuminate\Foundation\Http\FormRequest;

class PatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('update-address');
    }

    public function rules(): array
    {
        return [
            'address_line1' => 'sometimes|string|max:255',
            'address_line2' => 'sometimes|nullable|string|max:255',
            'postal_code'   => 'sometimes|nullable|string|max:20',
            'is_default'    => 'sometimes|boolean',
            'type'          => 'sometimes|string|max:50',
            'contact_name'  => 'sometimes|string|max:255',
            'municipality_id' => 'sometimes|integer|exists:municipalities,id',
            'alias'         => 'sometimes|string|max:255',
            'phone'         => 'sometimes|string|max:30',
        ];
    }
}