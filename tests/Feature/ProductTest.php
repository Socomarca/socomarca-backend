<?php

use App\Models\Product;

beforeEach(function ()
{
    createPrice();
    createCategory();
    createBrand();

    $this->user = createUser();
    $this->product = createProduct();
});

/**
 * Prueba que valida que el token sea obligatorio.
 */
test('validate_token', function ()
{
    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/products');

    $response->assertStatus(401);
});

/**
 * Prueba de respuesta exitosa.
 */
test('validate_status_code_200', function ()
{
    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/products');

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
                    'category' =>
                    [
                        'id',
                        'name',
                        'description',
                        'code',
                        'level',
                        'key',
                        'created_at',
                        'updated_at',
                    ],
                    'subcategory' =>
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
                    'brand' =>
                    [
                        'id',
                        'name',
                        'description',
                        'logo_url',
                        'created_at',
                        'updated_at',
                    ],
                    'price',
                    'sku',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ),
        ]);
});

/**
 * Prueba que valida que el campo id en params sea válido en la tabla products.
 */
test('test_product_not_found', function ()
{
    $id = $this->product->id;

    Product::truncate();

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/products/' . $id);

    $response->assertStatus(404);
});
