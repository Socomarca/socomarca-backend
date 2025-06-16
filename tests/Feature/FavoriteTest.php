<?php

use App\Models\Favorite;
use App\Models\FavoriteList;
use App\Models\Product;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

test('query of user favorites failed authentication with no user', function () {
    $route = route('favorites.index');
    $this->getJson($route)->assertStatus(401);
});

test('successful query of user favorites', function () {
    $route = route('favorites.index');
    $user = User::factory()
        ->has(
            FavoriteList::factory()
                ->has(Favorite::factory()->count(15))
                ->count(2),
            'favoritesList'
        )
        ->create();

    $favorite = $user->favoritesList()->first()->favorites()->first();

    $this->actingAs($user, 'sanctum')
        ->getJson($route)
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                    'favorites' => [
                        [
                            'id',
                            'product' => [
                                'id',
                                'name',
                                'description',
                                'sku',
                                'status',
                                'created_at',
                                'updated_at',
                            ],
                            'created_at',
                            'updated_at',
                        ]
                    ],
                ],
            ],
        ])
        ->assertJson(
            fn(AssertableJson $json) =>
            $json
                ->has('data')
                ->has('data.0.favorites')
                ->where('data.0.favorites.0.id', fn($id) => Favorite::find($id)->favoriteList->user->id == $user->id)
                ->where('data.0.favorites.1.id', fn($id) => Favorite::find($id)->favoriteList->user->id == $user->id)
                ->where('data.1.favorites.0.id', fn($id) => Favorite::find($id)->favoriteList->user->id == $user->id)
                ->where('data.1.favorites.1.id', fn($id) => Favorite::find($id)->favoriteList->user->id == $user->id)
                ->etc()
        );
});

test('favorite store error because of validation failure', function () {
    $user = User::factory()->create();
    $route = route('favorites.store');

    $this->actingAs($user, 'sanctum')
        ->postJson($route)
        ->assertInvalid(['favorite_list_id', 'product_id']);

    // TODO Complementar aserciones
});

test('new favorite list succesfully stored', function () {
    $route = route('favorites.store');
    $user = User::factory()->has(
        FavoriteList::factory(),
        'favoritesList'
    )
        ->create();
    $product = Product::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson($route, [
            'favorite_list_id' => $user->favoritesList()->first()->id,
            'product_id' => $product->id
        ])
        ->assertCreated();

    $route = route('favorites.index');

    $this->actingAs($user, 'sanctum')
        ->getJson($route)
        ->assertOk()
        ->assertJson(
            fn(AssertableJson $json) =>
            $json
                ->has('data')
                ->has('data.0.favorites')
                ->where('data.0.favorites.0.id', fn($id) => Favorite::find($id)->favoriteList->user->id == $user->id)
                ->where('data.0.favorites.0.product.id', fn($id) => $product->id == $id)
                ->etc()
        );
});

test('favorite list deleted', function () {
    $route = route('favorites.store');
    $user = User::factory()->has(
        FavoriteList::factory(),
        'favoritesList'
    )
        ->create();
    $product = Product::factory()->create();

    $this
        ->actingAs($user, 'sanctum')
        ->postJson($route, [
            'favorite_list_id' => $user->favoritesList()->first()->id,
            'product_id' => $product->id
        ])
        ->assertCreated();

    $user->refresh();
    $favoritesQuantity = $user->favoritesList()->first()->favorites()->count();

    expect($favoritesQuantity == 1)->toBeTrue();

    $route = route('favorites.destroy', [
        'id' => $user->favoritesList()->first()->favorites()->first()->id,
    ]);

    $this
        ->actingAs($user, 'sanctum')
        ->deleteJson($route)
        ->assertOk();

    $user->refresh();
    $favoritesQuantity = $user->favoritesList()->first()->favorites()->count();

    expect($favoritesQuantity == 0)->toBeTrue();
});
