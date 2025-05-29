<?php

use App\Models\Subcategory;

beforeEach(function ()
{
    $this->user = createUser();
    $this->category = createCategory();
});

/**
 * Prueba que valida que el token sea obligatorio.
 */
test('validate_token', function ()
{
    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/subcategories');

    $response->assertStatus(401);
});

/**
 * Prueba de respuesta exitosa.
 */
test('validate_status_code_200', function ()
{
    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/subcategories');

    $response
        ->assertStatus(200)
        ->assertJson([]);
});

/**
 * Prueba que valida que el campo id en params sea un entero.
 */
test('test_id_is_integer', function ()
{
    $id = 'id';

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/subcategories/' . $id);

    $response->assertStatus(422);
});

/**
 * Prueba que valida que el campo id en params sea válido en la tabla subcategories.
 */
test('test_subcategory_not_found', function ()
{
    $id = $this->category->subCategories['0']->id;

    Subcategory::truncate();

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/subcategories/' . $id);

    $response->assertStatus(404);
});
