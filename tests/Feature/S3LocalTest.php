<?php
use Illuminate\Http\UploadedFile;

test('admin puede subir y listar archivos en S3 localstack con carpeta', function () {
    $user = \App\Models\User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user, 'sanctum');

    // Subir archivo con carpeta específica
    $response = $this->post('/api/s3-test-upload', [
        'file' => UploadedFile::fake()->create('prueba.txt', 1, 'text/plain'),
        'folder' => 'test-folder'
    ], [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['message', 'path', 'url']);

    // DEBUG: Ver qué path se generó
    $uploadedPath = $response->json('path');
    dump('Archivo subido en path:', $uploadedPath);

    // Listar archivos de esa carpeta específica
    $listResponse = $this->getJson('/api/s3-test-list?folder=test-folder');
    $listResponse->assertStatus(200);
    $listResponse->assertJsonStructure(['files', 'folder']);
    
    
    
    $this->assertTrue(
        collect($listResponse['files'])->contains(fn($f) => str_contains($f, 'prueba.txt'))
    );
});

test('admin puede subir y listar archivos en S3 localstack sin carpeta', function () {
    $user = \App\Models\User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user, 'sanctum');

    // Subir archivo sin especificar carpeta (usa 'test' por defecto)
    $response = $this->post('/api/s3-test-upload', [
        'file' => UploadedFile::fake()->create('archivo-root.txt', 1, 'text/plain'),
    ], [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['message', 'path', 'url']);

    // Listar todos los archivos del bucket
    $listResponse = $this->getJson('/api/s3-test-list');
    $listResponse->assertStatus(200);
    $listResponse->assertJsonStructure(['files', 'folder']);
    
    // Cambio aquí también
    $this->assertTrue(
        collect($listResponse['files'])->contains(fn($f) => str_contains($f, 'archivo-root.txt'))
    );
});