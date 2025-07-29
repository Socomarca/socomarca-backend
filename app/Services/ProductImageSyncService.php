<?php

namespace App\Services;

use App\Jobs\SyncProductImage;
use Illuminate\Http\UploadedFile;

class ProductImageSyncService
{
    public function sync(UploadedFile $zipFile)
    {
        // Guarda el archivo ZIP temporalmente
        $zipPath = $zipFile->store('product-sync', 'local');
        // Encola el job para procesar el archivo ZIP
        SyncProductImage::dispatch($zipPath);
    }
}