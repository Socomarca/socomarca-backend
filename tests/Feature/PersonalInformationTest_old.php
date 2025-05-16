<?php

/** COMANDO PARA EJECUTAR ESTO: ./vendor/bin/pest */

use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('puede crear un usuario con datos válidos', function () {
    // Prepare
    $data = [
        'name' => 'Juan Perez',
        'email' => 'juan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => 912345678,
        'rut' => '12345678-9',
        'business_name' => 'Empresa de Prueba',
        'is_active' => true,
    ];

    // Action
    $response = $this->postJson('/api/users', $data);

    // Assets
    $response->assertStatus(200)->assertJson(['message' => 'The user has been added']);
});

test('falla si se omiten campos obligatorios al crear un usuario', function () {
    // Acción
    $response = $this->postJson('/api/users', []);

    // Aserción
    $response->assertStatus(422)
             ->assertJsonValidationErrors([
                 'name',
                 'email',
                 'password',
                 'phone',
                 'rut',
                 'business_name', 
                 'is_active',
             ]);
});

test('falla si el email no es válido', function () {
    // Preparación
    $data = User::factory()->make([
        'email' => 'correo-no-valido',
    ])->toArray();
    $data['password'] = 'password123';

    // Acción
    $response = $this->postJson('/api/users', $data);

    // Aserción
    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});

test('falla al crear un usuario si el email ya está registrado', function () {
    // Creamos un usuario con un email específico
    \App\Models\User::factory()->create([
        'email' => 'correo@duplicado.com',
    ]);

    // Intentamos crear otro usuario con el mismo email
    $data = [
        'name' => 'Juan Pérez',
        'email' => 'correo@duplicado.com', // Email duplicado
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => 912345678,
        'rut' => '12345678-9',
        'business_name' => 'Empresa de Prueba',
        'is_active' => true,
    ];

    $response = $this->postJson('/api/users', $data);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});

// test('falla si el email ya está en uso', function () {
//     // Preparación
//     User::factory()->create(['email' => 'correo@repetido.com']);

//     $data = User::factory()->make([
//         'email' => 'correo@repetido.com',
//     ])->toArray();
//     $data['password'] = 'password123';

//     // Acción
//     $response = $this->postJson('/api/users', $data);

//     // Aserción
//     $response->assertStatus(422)
//              ->assertJsonValidationErrors(['email']);
// });

test('falla si el número de teléfono no tiene 9 dígitos', function () {
    // Preparación
    $data = User::factory()->make([
        'phone' => 12345,
    ])->toArray();
    $data['password'] = 'password123';

    // Acción
    $response = $this->postJson('/api/users', $data);

    // Aserción
    $response->assertStatus(422)
             ->assertJsonValidationErrors(['phone']);
});

test('falla si el RUT no tiene exactamente 10 caracteres', function () {
    // Preparación
    $data = User::factory()->make([
        'rut' => '1234567-9', // 9 caracteres
    ])->toArray();
    $data['password'] = 'password123';

    // Acción
    $response = $this->postJson('/api/users', $data);

    // Aserción
    $response->assertStatus(422)
             ->assertJsonValidationErrors(['rut']);
});

test('puede actualizar un usuario con datos válidos', function () {
    // Preparación: creamos un usuario
    $user = \App\Models\User::factory()->create([
        'email' => 'juan@example.com',
        'rut' => '12345678-9',
    ]);

    $data = [
        'name' => 'Juan Actualizado',
        'email' => 'juan_actualizado@example.com',
        'phone' => 987654321,
        'rut' => '12345678-9', // mismo RUT
        'business_name' => 'Empresa Actualizada',
        'is_active' => false,
    ];

    // Acción
    $response = $this->putJson("/api/users/{$user->id}", $data);

    // Aserción
    $response->assertStatus(200)
             ->assertJson(['message' => 'The selected user has been updated']);
});

test('falla al actualizar si el email no es válido', function () {
    $user = \App\Models\User::factory()->create();

    $data = [
        'name' => 'Nuevo Nombre',
        'email' => 'correo-no-valido',
        'phone' => 912345678,
        'rut' => '12345678-9',
        'business_name' => 'Empresa X',
        'is_active' => true,
    ];

    $response = $this->putJson("/api/users/{$user->id}", $data);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});

test('falla si se omiten campos requeridos al actualizar un usuario', function () {
    $user = \App\Models\User::factory()->create();

    $data = [];

    $response = $this->putJson("/api/users/{$user->id}", $data);

    $response->assertStatus(422)
             ->assertJsonValidationErrors([
                 'name', 'email', 'phone', 'rut', 'business_name', 'is_active'
             ]);
});

test('falla si el número de teléfono es inválido al actualizar', function () {
    $user = \App\Models\User::factory()->create();

    $data = [
        'name' => 'Nombre',
        'email' => 'correo@example.com',
        'phone' => 1234, // inválido
        'rut' => '12345678-9',
        'business_name' => 'Empresa',
        'is_active' => true,
    ];

    $response = $this->putJson("/api/users/{$user->id}", $data);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['phone']);
});

test('falla si el email ya está en uso por otro usuario al actualizar', function () {
    // Creamos dos usuarios
    $usuario1 = \App\Models\User::factory()->create([
        'email' => 'correo@existente.com',
    ]);

    $usuario2 = \App\Models\User::factory()->create([
        'email' => 'otro@correo.com',
    ]);

    // Intentamos actualizar el usuario2 con el mismo email que el usuario1
    $data = [
        'name' => 'Nombre Modificado',
        'email' => 'correo@existente.com', // Email duplicado
        'phone' => 987654321,
        'rut' => '12345678-9',
        'business_name' => 'Empresa',
        'is_active' => true,
    ];

    $response = $this->putJson("/api/users/{$usuario2->id}", $data);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});