<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        Log::info('SyncProductImage job iniciado', ['zipPath' => $this->zipPath]);
        
        
        $zipFullPath = storage_path('app/private/' . $this->zipPath);
        Log::info('Ruta completa del ZIP', ['zipFullPath' => $zipFullPath]);
        $extractPath = storage_path('app/private/product-sync/extracted_' . uniqid());
        $zip = new \ZipArchive;
        if ($zip->open($zipFullPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
            Log::info('ZIP extraído correctamente', ['extractPath' => $extractPath]);
        } else {
            Log::error('No se pudo abrir el ZIP', ['zipFullPath' => $zipFullPath]);
            return;
        }

        // Buscar el archivo Excel por extensión
        $excelPath = null;
        foreach (glob($extractPath . '/*.{xlsx,xls,csv}', GLOB_BRACE) as $file) {
            $excelPath = $file;
            break;
        }
        $imagesPath = $extractPath . '/images';

        if (!$excelPath || !file_exists($excelPath)) {
            Log::error('Archivo Excel no encontrado', ['extractPath' => $extractPath]);
            return;
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelPath);
            Log::info('Archivo Excel cargado correctamente', ['excelPath' => $excelPath]);
        } catch (\Throwable $e) {
            Log::error('Error al cargar archivo Excel', ['error' => $e->getMessage()]);
            return;
        }

        $sheet = $spreadsheet->getActiveSheet();
        foreach ($sheet->getRowIterator(2) as $row) { // Asumiendo encabezado en la fila 1
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getValue();
            }
            // $cells[0] = SKU, $cells[4] = nombre de la imagen
            $sku = $cells[0] ?? null;
            $imageName = $cells[4] ?? null;

            if (!$sku || !$imageName) {
                Log::warning("Fila inválida en archivo.xlsx: " . json_encode($cells));
                continue;
            }

            $localImagePath = $imagesPath . '/' . $imageName;
            if (!file_exists($localImagePath)) {
                Log::warning("Imagen no encontrada: $localImagePath");
                continue;
            }

            $s3Path = 'products/' . $imageName;
            Storage::disk('s3')->put($s3Path, file_get_contents($localImagePath));
            
            $url = Storage::disk('s3')->url($s3Path);
            if (app()->environment('local')) {
                $url = str_replace('localstack:4566', 'localhost:4566', $url);
            }

            // Busca el producto por SKU
            $product = \App\Models\Product::where('sku', $sku)->first();
            if ($product) {
                $product->image = $url; 
                $product->save();
            } else {
                Log::warning("Producto no encontrado para SKU: $sku");
            }
        }
    }
}