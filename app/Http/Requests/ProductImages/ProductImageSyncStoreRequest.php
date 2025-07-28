<?php

namespace App\Http\Requests\ProductImages;

use Illuminate\Foundation\Http\FormRequest;

class ProductImageSyncStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Ajusta según tu lógica de permisos
    }

    public function rules()
    {
        return [
            'sync_file' => 'required|file|mimes:zip|max:10240', // 10MB máx
        ];
    }
}