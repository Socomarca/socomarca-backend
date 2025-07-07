<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

test('command executes job immediately', function () {
    Log::shouldReceive('info')
        ->with('SyncRandomCategories started')
        ->once();
    Log::shouldReceive('info')
        ->with('SyncRandomCategories finished')
        ->once();

    $this->artisan('random:sync-categories')
        ->expectsOutput('Iniciando sincronización de categorías...')
        ->expectsOutput('Proceso de sincronización encolado correctamente.')
        ->assertExitCode(0);
});

test('command executes job and creates categories', function () {
    Log::shouldReceive('info')
        ->with('SyncRandomCategories started')
        ->once();
    Log::shouldReceive('info')
        ->with('SyncRandomCategories finished')
        ->once();

    $this->artisan('random:sync-categories')
        ->expectsOutput('Iniciando sincronización de categorías...')
        ->expectsOutput('Proceso de sincronización encolado correctamente.')
        ->assertExitCode(0);

    // Ajusta estos códigos a los que realmente inserta tu job
    $this->assertDatabaseHas('categories', [
        'code' => 'ACTF',
    ]);
    $this->assertDatabaseHas('subcategories', [
        'code' => 'ASES',
    ]);
});