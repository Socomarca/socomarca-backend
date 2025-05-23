<?php

use App\Models\Category;
use App\Models\User;

/**
 * Prueba de respuesta exitosa.
 */
test('validate_status_code_200', function ()
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
test('validate_id_is_required', function ()
{
    $user = User::factory()->create();

    Category::factory()->create();

    $id = '1';

    $response = $this->actingAs($user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/categories/' . $id);

    $response->assertStatus(200);
});

/**
 * Prueba que valida que el token sea obligatorio.
 */
test('validate_token', function ()
{
    $id = '1';

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/categories/' . $id);

    $response->assertStatus(401);
});

/**
 * Prueba que valida que el campo category en params sea un entero.
 */
test('validate_id_is_integer', function ()
{
    $id = 'a';

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/categories/' . $id);

    $response->assertStatus(401);
});

/**
 * Prueba que valida que el campo category en params sea válido en la base de datos.
 */
test('validate_category_not_found', function ()
{
    $user = User::factory()->create();

    $id = '999';

    $response = $this->actingAs($user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/categories/' . $id);

    $response->assertStatus(404);
});
