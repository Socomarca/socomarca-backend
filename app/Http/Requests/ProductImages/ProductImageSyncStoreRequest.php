<?php

namespace App\Http\Requests\ProductImages;

use App\Models\Siteinfo;
use Illuminate\Foundation\Http\FormRequest;

class ProductImageSyncStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        $uploadSettings = Siteinfo::where('key', 'upload_settings')->first();
        $maxUploadSize = $uploadSettings ? ($uploadSettings->value['max_upload_size'] ?? 50) : 50;
        
        // Convertir de MB a KB para la validación de Laravel
        $maxUploadSizeKB = $maxUploadSize * 1024;

        return [
            'sync_file' => "required|file|mimes:zip|max:{$maxUploadSizeKB}",
        ];
    }

    public function messages()
    {
        $uploadSettings = Siteinfo::where('key', 'upload_settings')->first();
        $maxUploadSize = $uploadSettings ? ($uploadSettings->value['max_upload_size'] ?? 50) : 50;

        return [
            'sync_file.max' => "El archivo no puede ser mayor a {$maxUploadSize} MB.",
            'sync_file.required' => 'El archivo es requerido.',
            'sync_file.file' => 'Debe ser un archivo válido.',
            'sync_file.mimes' => 'El archivo debe ser de tipo ZIP.',
        ];
    }
}