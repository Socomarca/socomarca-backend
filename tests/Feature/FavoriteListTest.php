<?php

use App\Models\FavoriteList;
use App\Models\User;

beforeEach(function ()
{
    $this->user = createUserHasFavoriteList();
});

/**
 * Prueba que valida que el token sea obligatorio.
 */
test('validate_token', function ()
{
    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/favorites-list');

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
        ->get('/api/favorites-list?user_id=' . $userId);

    $response
        ->assertStatus(200)
        ->assertJsonStructure(
        [
            'data' => array
            (
                [
                    'id',
                    'name',
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
                    'favorites',
                    'created_at',
                    'updated_at',
                ],
            ),
        ]);
});

/**
 * Prueba que valida que el campo user_id en query params sea válido en la tabla favorites_list.
 */
test('test_user_is_invalid', function ()
{
    $userId = $this->user->id;

    User::truncate();

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/favorites-list?user_id=' . $userId);

    $response->assertStatus(422);
});

/**
 * Prueba que valida que el campo id en params sea válido en la tabla favorites_list.
 */
test('test_favorites_list_not_found', function ()
{
    $id = $this->user->favoritesList['0']->id;

    FavoriteList::truncate();

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/favorites-list/' . $id);

    $response->assertStatus(404);
});
