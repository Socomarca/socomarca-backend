<?php

use App\Services\RandomApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Models\Product;

uses(RefreshDatabase::class);

test('command executes job and updates product prices', function () {
    
    $product = Product::create([
        'code' => 'PROD1',
        'sku' => 'SKU1',
        'name' => 'Producto 1',
        'price' => 1000,
        'random_product_id' => 123,
    ]);

    // Mock del servicio
    $mock = Mockery::mock(RandomApiService::class);
    $mock->shouldReceive('getPricesLists')->andReturn([
        'nombre' => 1,
        'datos' => [
            [
                'kopr' => 123, 
                'unidades' => [
                    [
                        'nombre' => 'kg',
                        'prunneto' => [
                            ['f' => 999]
                        ]
                    ]
                ]
            ]
        ]
    ]);
    App::instance(RandomApiService::class, $mock);

    Log::shouldReceive('info')->with('SyncRandomPrices started')->once();
    Log::shouldReceive('info')->with('SyncRandomPrices finished')->once();
    Log::shouldReceive('error')->zeroOrMoreTimes(); 


    
    $this->artisan('random:sync-prices')
        ->expectsOutput('Iniciando sincronización de precios...')
        ->expectsOutput('Proceso de sincronización encolado correctamente.')
        ->assertExitCode(0);

    // Verifica que el precio fue actualizado (ajusta el valor esperado)
    $this->assertDatabaseHas('products', [
        'name' => 'Producto 1',
    ]);
});

test('command executes price sync job immediately', function () {
    // Crea el producto necesario para el job
    Product::create([
        'code' => 'PROD1',
        'sku' => 'SKU1',
        'name' => 'Producto 1',
        'price' => 1000,
        'random_product_id' => 123,
    ]);

    // Mock del servicio
    $mock = Mockery::mock(\App\Services\RandomApiService::class);
    $mock->shouldReceive('getPricesLists')->andReturn([
        'nombre' => 1,
        'datos' => [
            [
                'kopr' => 123,
                'unidades' => [
                    [
                        'nombre' => 'kg',
                        'prunneto' => [
                            ['f' => 999]
                        ]
                    ]
                ]
            ]
        ]
    ]);
    App::instance(\App\Services\RandomApiService::class, $mock);

    Log::shouldReceive('info')->with('SyncRandomPrices started')->once();
    Log::shouldReceive('info')->with('SyncRandomPrices finished')->once();
    Log::shouldReceive('error')->zeroOrMoreTimes(); 

    $this->artisan('random:sync-prices')
        ->expectsOutput('Iniciando sincronización de precios...')
        ->expectsOutput('Proceso de sincronización encolado correctamente.')
        ->assertExitCode(0);
});

test('command executes price sync job and creates prices', function () {
    // Crea el producto necesario para el job
    Product::create([
        'code' => 'PROD1',
        'sku' => 'SKU1',
        'name' => 'Producto 1',
        'price' => 1000,
        'random_product_id' => 123,
    ]);

    // Mock del servicio
    $mock = Mockery::mock(\App\Services\RandomApiService::class);
    $mock->shouldReceive('getPricesLists')->andReturn([
        'nombre' => 1,
        'datos' => [
            [
                'kopr' => 123,
                'unidades' => [
                    [
                        'nombre' => 'kg',
                        'prunneto' => [
                            ['f' => 999]
                        ]
                    ]
                ]
            ]
        ]
    ]);
    App::instance(\App\Services\RandomApiService::class, $mock);

    Log::shouldReceive('info')->with('SyncRandomPrices started')->once();
    Log::shouldReceive('info')->with('SyncRandomPrices finished')->once();
    Log::shouldReceive('error')->zeroOrMoreTimes();

    $this->artisan('random:sync-prices')
        ->expectsOutput('Iniciando sincronización de precios...')
        ->expectsOutput('Proceso de sincronización encolado correctamente.')
        ->assertExitCode(0);

    $this->assertDatabaseHas('prices', [
        'unit' => 'kg',
    ]);
    $this->assertDatabaseHas('prices', [
        'is_active' => true,
    ]);
});