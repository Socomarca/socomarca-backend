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
        $zipFullPath = storage_path('app/' . $this->zipPath);
        $extractPath = storage_path('app/product-sync/extracted_' . uniqid());
        $zip = new \ZipArchive;
        if ($zip->open($zipFullPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            return;
        }

        $excelPath = $extractPath . '/sync_map.xlsx';
        $imagesPath = $extractPath . '/images';

        // Leer el Excel y obtener el mapeo (ejemplo básico)
        // Debes instalar phpoffice/phpspreadsheet para esto
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelPath);
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
                Log::warning("Fila inválida en sync_map.xlsx: " . json_encode($cells));
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

            // Busca el producto por SKU
            $product = \App\Models\Product::where('sku', $sku)->first();
            if ($product) {
                $product->image_url = $url; // Ajusta el campo según tu modelo
                $product->save();
            } else {
                Log::warning("Producto no encontrado para SKU: $sku");
            }
        }
    }
}