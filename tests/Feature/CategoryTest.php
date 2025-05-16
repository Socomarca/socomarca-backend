<?php

use App\Models\User;

/**
 * Prueba de respuesta exitosa.
 */
test('validate_category_status_ok', function ()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/categories');

    $response->assertStatus(200);
});

/**
 * Prueba que valida que el campo category en params es obligatorio.
 */
test('validate_category_is_required', function ()
{
    $user = User::factory()->create();

    $category = '1';

    $response = $this->actingAs($user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/categories/' . $category);

    $response->assertStatus(200);
});

/**
 * Prueba que valida que el token sea obligatorio.
 */
test('validate_bearer_token', function ()
{
    $category = '1';

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/categories/' . $category);

    $response->assertStatus(401);
});

/**
 * Prueba que valida que el campo category en params sea un entero.
 */
test('validate_category_is_integer', function ()
{
    $category = 'a';

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/categories/' . $category);

    $response->assertStatus(401);
});

/**
 * Prueba que valida que el campo category en params sea válido en la base de datos.
 */
test('validate_category_is_invalid', function ()
{
    $user = User::factory()->create();

    $category = '999';

    $response = $this->actingAs($user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/categories/' . $category);

    $response->assertStatus(422);
});
