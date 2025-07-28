<?php

namespace App\Services;

use App\Jobs\SyncProductImage;
use Illuminate\Http\UploadedFile;

class ProductImageSyncService
{
    public function sync(UploadedFile $zipFile)
    {
        // Encola el job para procesar el archivo ZIP
        SyncProductImage::dispatch($zipFile->store('product-sync', 'local'));
    }
}