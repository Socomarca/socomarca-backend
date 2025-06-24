<?php

use App\Models\FavoriteList;
use App\Models\User;

test('query of user favorites lists failed authentication with no user', function ()
{
    $route = route('favorites-list.index');
    $this->getJson($route)->assertStatus(401);
});

test('successful query of user favorites lists', function ()
{
    $user = User::factory()->has(FavoriteList::factory(), 'favoritesList')
        ->create();
    $route = route('favorites-list.index');

    $response = $this->actingAs($user, 'sanctum')
        ->getJson($route);

    $favoriteList = $user->favoritesList()->first();

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                    'user_id',
                ],
            ],
        ])
        ->assertJsonFragment([
            'id' => $favoriteList->id,
            'name' => $favoriteList->name,
        ]);
});

test('favorites list not found', function ()
{
    $user = User::factory()->create();
    $route = route('favorites-list.show', ['favoriteList' => 4304993]);
    $this->actingAs($user, 'sanctum')
        ->getJson($route)
        ->assertNotFound();
});

test('favorite list store error because of validation failure', function () {
    $user = User::factory()->create();
    $route = route('favorites-list.store');

    $this->actingAs($user, 'sanctum')
        ->postJson($route)
        ->assertInvalid(['name']);
});

test('new favorite list succesfully stored', function () {
    $user = User::factory()->create();
    $route = route('favorites-list.store');
    $this->actingAs($user, 'sanctum')
        ->postJson($route, ['name' => 'Nueva lista favorita'])
        ->assertCreated();

    $route = route('favorites-list.index');
    $newList = $this->actingAs($user, 'sanctum')
        ->getJson($route)
        ->json('data.0');

    $user = User::find($user->id);
    $list = $user->favoritesList()->first();
    expect($newList['id'] == $list->id)->toBeTrue();
    expect($newList['name'] == $list->name)->toBeTrue();
});

test('favorite list updated', function () {
    $user = User::factory()->has(FavoriteList::factory(), 'favoritesList')
        ->create();
    $route = route('favorites-list.update', [
        'id' => $user->favoritesList()->first()->id
    ]);
    $newListName = 'Nueva lista de favoritos actualizada';

    $this->actingAs($user, 'sanctum')
        ->putJson($route, ['name' => $newListName])
        ->assertOk();

    $list = FavoriteList::find($user->favoritesList()->first()->id);
    expect($list->name == $newListName)->toBeTrue();
});
