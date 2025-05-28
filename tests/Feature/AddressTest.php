<?php

use App\Models\Address;

beforeEach(function ()
{
    $this->user = createAddress();
});

/**
 * Prueba que valida que el token sea obligatorio.
 */
test('validate_token', function ()
{
    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/addresses/');

    $response->assertStatus(401);
});

/**
 * Prueba de respuesta exitosa.
 */
test('validate_status_code_200', function ()
{
    $userId = $this->user->id;

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/addresses?user_id=' . $userId);

    $response->assertStatus(200);
});

/**
 * Prueba que valida que el campo user_id en query params sea válido en la tabla addresses.
 */
test('test_user_is_invalid', function ()
{
    Address::truncate();

    $userId = $this->user->id;

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/addresses?user_id=' . $userId);

    $response->assertStatus(422);
});

/**
 * Prueba que valida que el campo id en params sea un entero.
 */
test('test_id_is_integer', function ()
{
    $id = 'a';

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/addresses/' . $id);

    $response->assertStatus(422);
});

/**
 * Prueba que valida que el campo id en params sea válido en la tabla addresses.
 */
test('test_address_not_found', function ()
{
    Address::truncate();

    $id = '1';

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/addresses/' . $id);

    $response->assertStatus(404);
});
