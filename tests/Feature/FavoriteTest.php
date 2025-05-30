<?php

use App\Models\Favorite;
use App\Models\FavoriteList;

beforeEach(function ()
{
    createPrice();
    createCategory();
    createBrand();
    createProduct();

    $this->user = createFavorite();
});

/**
 * Prueba que valida que el token sea obligatorio.
 */
test('validate_token', function ()
{
    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/favorites');

    $response->assertStatus(401);
});

/**
 * Prueba de respuesta exitosa.
 */
test('validate_status_code_200', function ()
{
    $userId = $this->user->id;
    $favoriteListId = $this->user->favoritesList['0']->id;

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/favorites?user_id=' . $userId . '&favorite_list_id=' . $favoriteListId);

    $response
        ->assertStatus(200)
        ->assertJson([]);
});

/**
 * Prueba que valida que el campo user_id en query params sea válido en la tabla favorites_list.
 */
test('test_user_is_invalid', function ()
{
    $userId = $this->user->id;
    $favoriteListId = $this->user->favoritesList['0']->id;

    FavoriteList::truncate();

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/favorites?user_id=' . $userId . '&favorite_list_id=' . $favoriteListId);

    $response->assertStatus(422);
});

/**
 * Prueba que valida que el campo favorite_list_id en query params sea válido en la tabla favorites.
 */
test('test_favorite_list_is_invalid', function ()
{
    $userId = $this->user->id;
    $favoriteListId = $this->user->favoritesList['0']->id;

    Favorite::truncate();

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/favorites?user_id=' . $userId . '&favorite_list_id=' . $favoriteListId);

    $response->assertStatus(422);
});
