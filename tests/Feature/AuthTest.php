<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('usuario puede iniciar sesion con credenciales validas', function () {
    // Preparación
    User::factory()->create([
        'rut' => '17260847-7',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    // Acción
    $response = $this->postJson(route('login'), [
        'rut' => '17260847-7',
        'password' => 'password123',
        'device_name' => 'test-device',
    ]);

    // Aserción
    $response->assertStatus(200);

});

test('usuario no puede iniciar sesion con credenciales invalidas', function () {
    // Preparación
    User::factory()->create([
        'rut' => '11111111-1',
        'password' => Hash::make('password123'),
    ]);

    // Acción
    $response = $this->postJson(route('login'), [
        'rut' => '11111111-1',
        'password' => 'wrongpassword',
        'device_name' => 'test-device',
    ]);

    // Aserción
    $response->assertStatus(401);
});

test('usuario inactivo no puede iniciar sesion', function () {
    // Preparación
    User::factory()->create([
        'rut' => '22222222-2',
        'password' => Hash::make('password123'),
        'is_active' => false,
    ]);

    // Acción
    $response = $this->postJson(route('login'), [
        'rut' => '22222222-2',
        'password' => 'password123',
        'device_name' => 'test-device',
    ]);

    // Aserción
    $response->assertStatus(403)
        ->assertJson([
            'status' => false,
            'message' => 'Tu cuenta está desactivada. Por favor contacta con soporte.',
        ]);
    $this->assertGuest();
});

test('usuario puede cerrar sesion', function () {
    // Preparación
    $user = User::factory()->create([
        'rut' => '11111111-1',
        'password' => Hash::make('password123'),
    ]);
    
    Sanctum::actingAs($user);

    // Acción
    $response = $this->deleteJson(route('destroy'));

    // Aserción
    $response->assertStatus(200);
});

test('usuario autenticado puede obtener su informacion', function () {
    // Preparación
    $user = User::factory()->create([
        'rut' => '33333333-3'
    ]);
    Sanctum::actingAs($user);

    // Acción
    $response = $this->getJson(route('me'));

    // Aserción
    $response->assertStatus(200);
}); 