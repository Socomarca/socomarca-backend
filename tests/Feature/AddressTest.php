<?php

use App\Models\Address;

beforeEach(function ()
{
    $this->user = createUserHasAddress();
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

    $response
        ->assertStatus(200)
        ->assertJsonStructure(
        [
            'data' => array
            (
                [
                    'id',
                    'address_line1',
                    'address_line2',
                    'postal_code',
                    'is_default',
                    'type',
                    'phone',
                    'contact_name',
                    'user' =>
                    [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'rut',
                        'business_name',
                        'created_at',
                        'updated_at',
                    ],
                    'municipality' =>
                    [
                        'id',
                        'name',
                        'code',
                        'region_id',
                        'created_at',
                        'updated_at',
                    ],
                    'created_at',
                    'updated_at',
                ],
            ),
        ]);
});

/**
 * Prueba que valida que el campo user_id en query params sea válido en la tabla addresses.
 */
test('test_user_is_invalid', function ()
{
    $userId = $this->user->id;

    Address::truncate();

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/addresses?user_id=' . $userId);

    $response->assertStatus(422);
});

// /**
//  * Prueba que valida que el campo id en params sea un entero.
//  */
// test('test_id_is_integer', function ()
// {
//     $id = 'id';

//     $response = $this->actingAs($this->user, 'sanctum')
//         ->withHeaders(['Accept' => 'application/json'])
//         ->get('/api/addresses/' . $id);

//     $response->assertStatus(422);
// });

/**
 * Prueba que valida que el campo id en params sea válido en la tabla addresses.
 */
test('test_address_not_found', function ()
{
    $id = $this->user->addresses['0']->id;

    Address::truncate();

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/addresses/' . $id);

    $response->assertStatus(404);
});
