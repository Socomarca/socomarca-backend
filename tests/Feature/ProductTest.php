<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\FavoriteList;
use App\Models\Price;
use App\Models\Product;
use App\Models\Subcategory;

beforeEach(function () {
    $this->user = createUser();

    // Estructura de la respuesta para la búsqueda, incluyendo los filtros devueltos
    $this->searchResponseStructure = [
        'data' => [
            '*' => [
                'id',
                'name',
                'unit',
                'price',
                'stock',
                'image',
                'sku',
                'is_favorite',
                'category' => ['id', 'name'],
                'subcategory' => ['id', 'name'],
                'brand' => ['id', 'name'],
            ],
        ],
        'meta', // Estructura de paginación
        'filters' => [ // Filtros devueltos
            'min_price',
            'max_price',
        ]
    ];
});

test('search fails if price range is missing', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/products/search', [
            'filters' => [
                'name' => 'un producto cualquiera' 
            ]
        ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrorFor('filters.price');
});

test('verifies products price range filter works correctly', function () {
    Product::truncate();
    $minSearch = 60000;
    $maxSearch = 90000;

    // Crea productos, uno de ellos garantizado dentro del rango
    Product::factory()->has(Price::factory(['price' => 75000, 'is_active' => true]))->create();
    Product::factory()->has(Price::factory(['price' => 40000, 'is_active' => true]))->create(); // Fuera de rango

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/products/search', [
            'filters' => [
                'price' => [
                    'min' => $minSearch,
                    'max' => $maxSearch,
                ]
            ]
        ]);

    $response->assertStatus(200)
             ->assertJsonStructure($this->searchResponseStructure);

    expect($response->json('data'))->not->toBeEmpty();
    foreach ($response->json('data') as $product) {
        expect($product['price'])->toBeGreaterThanOrEqual($minSearch);
        expect($product['price'])->toBeLessThanOrEqual($maxSearch);
    }
});

test('verifies optional filters for category, subcategory, brand, and name', function () {
    Product::truncate();
    $category = Category::factory()->create();
    $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);
    $brand = Brand::factory()->create();

    // Producto que coincide con todos los filtros
    Product::factory()
        ->has(Price::factory(['price' => 5000, 'is_active' => true]))
        ->create([
            'name' => 'Producto Estrella',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'brand_id' => $brand->id,
        ]);
    
    // Producto que no coincide
    Product::factory()->has(Price::factory(['price' => 5000, 'is_active' => true]))->create(['name' => 'Otro Producto']);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/products/search', [
            'filters' => [
                'price' => ['min' => 1000, 'max' => 10000], // Rango obligatorio
                'category_id' => $category->id,
                'subcategory_id' => $subcategory->id,
                'brand_id' => $brand->id,
                'name' => 'Estrella', // Búsqueda parcial por nombre
            ]
        ]);

    $response->assertStatus(200)
             ->assertJsonStructure($this->searchResponseStructure);
    
    // Debería encontrar solo 1 producto
    expect($response->json('data'))->toHaveCount(1);
    $foundProduct = $response->json('data.0');
    expect($foundProduct['name'])->toBe('Producto Estrella');
    expect($foundProduct['category']['id'])->toBe($category->id);
    expect($foundProduct['subcategory']['id'])->toBe($subcategory->id);
    expect($foundProduct['brand']['id'])->toBe($brand->id);
});


test('verifies products list authorization', function () {
    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/products');
    $response->assertStatus(401);
});

test('verifies that inexistent product is not found', function () {
    Product::truncate();
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/products/1');
    $response->assertStatus(404);
});

test('verifies is_favorite filter works correctly', function () {
    // 1. Setup: Crear un usuario, su lista de favoritos y dos productos.
    $user = $this->user;
    $favoriteList = FavoriteList::factory()->create(['user_id' => $user->id]);

    // Producto 1: Será el favorito
    $favoriteProduct = Product::factory()
        ->has(Price::factory(['price' => 5000]))
        ->create();
    Favorite::factory()->create([
        'favorite_list_id' => $favoriteList->id,
        'product_id' => $favoriteProduct->id,
    ]);

    // Producto 2: No será favorito
    $nonFavoriteProduct = Product::factory()
        ->has(Price::factory(['price' => 6000]))
        ->create();

    // 2. Probar con is_favorite: true
    $responseFavorite = $this->actingAs($user, 'sanctum')
        ->postJson('/api/products/search', [
            'filters' => [
                'price' => ['min' => 1000, 'max' => 10000], // Rango obligatorio
                'is_favorite' => true,
            ]
        ]);

    $responseFavorite->assertStatus(200);
    // Debería encontrar solo el producto favorito
    expect($responseFavorite->json('data'))->toHaveCount(1);
    expect($responseFavorite->json('data.0.id'))->toBe($favoriteProduct->id);
    expect($responseFavorite->json('data.0.is_favorite'))->toBeTrue();

    // 3. Probar con is_favorite: false
    $responseNonFavorite = $this->actingAs($user, 'sanctum')
        ->postJson('/api/products/search', [
            'filters' => [
                'price' => ['min' => 1000, 'max' => 10000], // Rango obligatorio
                'is_favorite' => false,
            ]
        ]);
    
    $responseNonFavorite->assertStatus(200);
    // Debería encontrar solo el producto que NO es favorito
    expect($responseNonFavorite->json('data'))->toHaveCount(1);
    expect($responseNonFavorite->json('data.0.id'))->toBe($nonFavoriteProduct->id);
    expect($responseNonFavorite->json('data.0.is_favorite'))->toBeFalse();
});