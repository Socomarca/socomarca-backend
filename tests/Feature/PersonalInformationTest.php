<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Helper para autenticarse fácilmente
function authenticatedUser(): User {
    return User::factory()->create();
}

test('puede crear un usuario con datos válidos', function () {
    $user = authenticatedUser();

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

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', $data);

    $response->assertStatus(200)
             ->assertJson(['message' => 'The user has been added']);
});

test('falla si se omiten campos obligatorios al crear un usuario', function () {
    $user = authenticatedUser();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', []);

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
    $user = authenticatedUser();

    $data = User::factory()->make([
        'email' => 'correo-no-valido',
    ])->toArray();
    $data['password'] = 'password123';
    $data['password_confirmation'] = 'password123';

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', $data);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});

test('falla al crear un usuario si el email ya está registrado', function () {
    $user = authenticatedUser();

    User::factory()->create(['email' => 'correo@duplicado.com']);

    $data = [
        'name' => 'Juan Pérez',
        'email' => 'correo@duplicado.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => 912345678,
        'rut' => '12345678-9',
        'business_name' => 'Empresa de Prueba',
        'is_active' => true,
    ];

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', $data);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});

test('falla si el número de teléfono no tiene 9 dígitos', function () {
    $user = authenticatedUser();

    $data = User::factory()->make(['phone' => 12345])->toArray();
    $data['password'] = 'password123';
    $data['password_confirmation'] = 'password123';

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', $data);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['phone']);
});

test('falla si el RUT no tiene exactamente 10 caracteres', function () {
    $user = authenticatedUser();

    $data = User::factory()->make(['rut' => '1234567-9'])->toArray();
    $data['password'] = 'password123';
    $data['password_confirmation'] = 'password123';

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', $data);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['rut']);
});

test('puede actualizar un usuario con datos válidos', function () {
    $admin = authenticatedUser();

    $user = User::factory()->create([
        'email' => 'juan@example.com',
        'rut' => '12345678-9',
    ]);

    $data = [
        'name' => 'Juan Actualizado',
        'email' => 'juan_actualizado@example.com',
        'phone' => 987654321,
        'rut' => '12345678-9',
        'business_name' => 'Empresa Actualizada',
        'is_active' => false,
    ];

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/users/{$user->id}", $data);

    $response->assertStatus(200)
             ->assertJson(['message' => 'The selected user has been updated']);
});

test('falla al actualizar si el email no es válido', function () {
    $admin = authenticatedUser();
    $user = User::factory()->create();

    $data = [
        'name' => 'Nuevo Nombre',
        'email' => 'correo-no-valido',
        'phone' => 912345678,
        'rut' => '12345678-9',
        'business_name' => 'Empresa X',
        'is_active' => true,
    ];

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/users/{$user->id}", $data);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});

test('falla si se omiten campos requeridos al actualizar un usuario', function () {
    $admin = authenticatedUser();
    $user = User::factory()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/users/{$user->id}", []);

    $response->assertStatus(422)
             ->assertJsonValidationErrors([
                 'name', 'email', 'phone', 'rut', 'business_name', 'is_active'
             ]);
});

test('falla si el número de teléfono es inválido al actualizar', function () {
    $admin = authenticatedUser();
    $user = User::factory()->create();

    $data = [
        'name' => 'Nombre',
        'email' => 'correo@example.com',
        'phone' => 1234,
        'rut' => '12345678-9',
        'business_name' => 'Empresa',
        'is_active' => true,
    ];

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/users/{$user->id}", $data);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['phone']);
});

test('falla si el email ya está en uso por otro usuario al actualizar', function () {
    $admin = authenticatedUser();

    $usuario1 = User::factory()->create(['email' => 'correo@existente.com']);
    $usuario2 = User::factory()->create(['email' => 'otro@correo.com']);

    $data = [
        'name' => 'Nombre Modificado',
        'email' => 'correo@existente.com',
        'phone' => 987654321,
        'rut' => '12345678-9',
        'business_name' => 'Empresa',
        'is_active' => true,
    ];

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/users/{$usuario2->id}", $data);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});
