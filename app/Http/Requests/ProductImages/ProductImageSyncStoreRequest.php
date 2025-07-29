<?php

namespace App\Http\Requests\ProductImages;

use Illuminate\Foundation\Http\FormRequest;

class ProductImageSyncStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'sync_file' => 'required|file|mimes:zip|max:50240', // 50MB máx
        ];
    }
}