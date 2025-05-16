<?php

use App\Models\User;

/**
 * Prueba de respuesta exitosa.
 */
test('validate_product_status_ok', function ()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/products');

    $response->assertStatus(200);
});

/**
 * Prueba que valida que el campo product en params es obligatorio.
 */
test('validate_product_is_required', function ()
{
    $user = User::factory()->create();

    $product = '1';

    $response = $this->actingAs($user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/products/' . $product);

    $response->assertStatus(200);
});

/**
 * Prueba que valida que el token sea obligatorio.
 */
test('validate_bearer_token', function ()
{
    $product = '1';

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/products/' . $product);

    $response->assertStatus(401);
});

/**
 * Prueba que valida que el campo product en params sea un entero.
 */
test('validate_product_is_integer', function ()
{
    $product = 'a';

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/products/' . $product);

    $response->assertStatus(401);
});

/**
 * Prueba que valida que el campo product en params sea válido en la base de datos.
 */
test('validate_product_is_invalid', function ()
{
    $user = User::factory()->create();

    $product = '999';

    $response = $this->actingAs($user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/products/' . $product);

    $response->assertStatus(422);
});
