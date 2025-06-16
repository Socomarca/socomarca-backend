<?php

use App\Models\Address;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function ()
{
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

   
});

test('verify authentication for addresses list', function ()
{
    $response = $this->getJson('/api/addresses/');
    $response->assertUnauthorized();
});

test('verify user address list', function ()
{
    $addressCount = random_int(1, 5);
    Address::truncate();
    $user = User::factory()
        ->has(
            Address::factory()
                ->count($addressCount)
        )
            ->create();

    $user->assignRole('cliente');

    $route = route('addresses.index');
    $this->actingAs($user, 'sanctum')
        ->getJson($route)
        ->assertStatus(200)
        //->assertJsonStructure($this->addressListJsonStructure)
        ->assertJsonCount($addressCount, 'data');
});

test('verify customer cannot see other users addresses', function ()
{
    $addressCount = random_int(1, 5);
    Address::truncate();
    $user = User::factory()
        ->has(
            Address::factory()
                ->count($addressCount)
        )
            ->create();
    $address = $user->addresses()->first();

    $user->assignRole('cliente');

    $user2 = User::factory()->create();

    $route = route('addresses.show', ['address' => $address->id]);
    $this->actingAs($user, 'sanctum')
        ->getJson($route)
        ->assertStatus(200);

    $this->actingAs($user2, 'sanctum')
        ->getJson($route)
        ->assertStatus(403);
});

test('verify address not found', function ()
{
    Address::truncate();
    $user = User::factory()->create();
    $user->assignRole('cliente');

    $route = route('addresses.show', ['address' => 2]);
    $this->actingAs($user, 'sanctum')
        ->getJson($route)
        ->assertNotFound();
});

test('verify customer can add a new address', function () {
    Address::truncate();
    $user = User::factory()->create();
    $user->assignRole('cliente');

    $municipality = \App\Models\Municipality::factory()->create();

    $payload = [
        'address_line1' => 'Calle Falsa 123',
        'address_line2' => 'Depto 4B',
        'postal_code' => '1234567',
        'is_default' => true,
        'type' => 'shipping',
        'phone' => '987654321',
        'contact_name' => 'Juan Pérez',
        'municipality_id' => $municipality->id,
        'alias' => 'Casa',
    ];

    $route = route('addresses.store');

    $response = $this->actingAs($user, 'sanctum')
        ->postJson($route, $payload);

    $response->assertStatus(201)
        ->assertJsonFragment(['message' => 'The address has been added']);

    $this->assertDatabaseHas('addresses', [
        'address_line1' => 'Calle Falsa 123',
        'user_id' => $user->id,
        'municipality_id' => $municipality->id,
    ]);
});

test('verify customer can update an address', function () {
    Address::truncate();
    $user = User::factory()->create();
    $user->assignRole('cliente');

    $municipality = \App\Models\Municipality::factory()->create();
    $address = Address::factory()->create([
        'user_id' => $user->id,
        'municipality_id' => $municipality->id,
    ]);

    $payload = [
        'address_line1' => 'Nueva Calle 456',
        'address_line2' => 'Depto 8C',
        'postal_code' => 7654321,
        'is_default' => false,
        'type' => 'billing', 
        'phone' => 123456789,
        'contact_name' => 'Ana Gómez',
        'municipality_id' => $municipality->id,
        'alias' => 'Oficina',
    ];

    $route = route('addresses.update', ['address' => $address->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson($route, $payload);

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'The selected address has been updated']);

    $this->assertDatabaseHas('addresses', [
        'id' => $address->id,
        'address_line1' => 'Nueva Calle 456',
        'contact_name' => 'Ana Gómez',
    ]);
});

test('validate required fields when creating an address', function () {
    $user = \App\Models\User::factory()->create();
    $user->assignRole('cliente');

    $route = route('addresses.store');

    // Payload vacío para forzar errores de validación
    $payload = [];

    $response = $this->actingAs($user, 'sanctum')
        ->postJson($route, $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'address_line1',
            'address_line2',
            'postal_code',
            'is_default',
            'type',
            'phone',
            'contact_name',
            'municipality_id',
            'alias',
        ]);
});


test('validate invalid fields when creating an address', function () {
    $user = \App\Models\User::factory()->create();
    $user->assignRole('cliente');

    $route = route('addresses.store');

    $payload = [
        'address_line1' => '', // vacío, debe ser requerido
        'address_line2' => 123, // debe ser string
        'postal_code' => 'no-numero', // debe ser integer
        'is_default' => 'not-boolean', // debe ser boolean
        'type' => 'otro', // debe ser 'billing' o 'shipping'
        'phone' => 'abc', // debe ser integer y 9 dígitos
        'contact_name' => '', // requerido
        'municipality_id' => 999999, // no existe
        'alias' => str_repeat('a', 100), // excede max:50
    ];

    $response = $this->actingAs($user, 'sanctum')
        ->postJson($route, $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'address_line1',
            'address_line2',
            'postal_code',
            'is_default',
            'type',
            'phone',
            'contact_name',
            'municipality_id',
            'alias',
        ]);
});