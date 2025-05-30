<?php

use App\Models\Category;

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
        ->get('/api/categories');

    $response->assertStatus(401);
});

/**
 * Prueba de respuesta exitosa.
 */
test('validate_status_code_200', function ()
{
    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/categories');

    $response
        ->assertStatus(200)
        ->assertJsonStructure(
        [
            'data' => array
            (
                [
                    'id',
                    'name',
                    'description',
                    'code',
                    'level',
                    'key',
                    'subcategories' => array
                    (
                        [
                            'id',
                            'category_id',
                            'name',
                            'description',
                            'code',
                            'level',
                            'key',
                            'created_at',
                            'updated_at',
                        ],
                    ),
                    'created_at',
                    'updated_at',
                ],
            ),
        ]);
});

/**
 * Prueba que valida que el campo id en params sea válido en la tabla categories.
 */
test('test_category_not_found', function ()
{
    $id = $this->category->id;

    Category::truncate();

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/categories/' . $id);

    $response->assertStatus(404);
});
