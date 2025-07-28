<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncProductImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $zipPath;

    public function __construct($zipPath)
    {
        $this->zipPath = $zipPath;
    }

    public function handle()
    {
        // Aquí va el algoritmo de descompresión y sincronización
        // - Descomprimir el ZIP
        // - Leer sync_map.xlsx
        // - Actualizar imágenes de productos
    }
}