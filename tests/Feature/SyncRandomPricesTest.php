<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use App\Models\Product;

uses(RefreshDatabase::class);

test('command executes job and updates product prices', function () {
    // Crea productos de prueba
    $product = Product::create([
        'code' => 'PROD1',
        'sku' => 'SKU1',
        'name' => 'Producto 1',
        'price' => 1000,
    ]);

    // Simula logs si tu job los usa
    Log::shouldReceive('info')->with('SyncRandomPrices started')->once();
    Log::shouldReceive('info')->with('SyncRandomPrices finished')->once();
    Log::shouldReceive('error')->zeroOrMoreTimes(); 


    // Ejecuta el comando (ajusta el nombre si es diferente)
    $this->artisan('random:sync-prices')
        ->expectsOutput('Iniciando sincronización de precios...')
        ->expectsOutput('Proceso de sincronización encolado correctamente.')
        ->assertExitCode(0);

    // Verifica que el precio fue actualizado (ajusta el valor esperado)
    $this->assertDatabaseHas('products', [
        'code' => 'PROD1',
        // Cambia 2000 por el precio esperado después de la sincronización
        'price' => 2000,
    ]);
});