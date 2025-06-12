<?php

use App\Models\Price;
use App\Models\Product;

beforeEach(function () {
    $this->user = createUser();

    $this->productListJsonStructure = [
        'data' => [
            [
                'id',
                'name',
                'unit',
                'price',
                'stock',
                'image',
                'sku',
                'is_favorite',
                'category' => ['id', 'name',],
                'subcategory' => ['id', 'name',],
                'brand' => ['id', 'name',],
            ],
        ],
        'meta' => [
            'current_page',
            'from',
            'last_page',
            'path',
            'per_page',
            'to',
            'total',
            'links' => [
                ['url', 'label', 'active']
            ],
        ]
    ];
});

test('verifies products list authorization', function () {
    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/products');

    $response->assertStatus(401);
});

test('verifies products list response structure', function () {
    Product::truncate();
    $nActivePrices = random_int(3, 9); // Número de precios activos
    Product::factory()
        ->has(Price::factory(['is_active' => true])->count($nActivePrices))
        ->create();
    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/products');

    expect($response->json('data'))->toHaveCount($nActivePrices);
    $response
        ->assertStatus(200)
        ->assertJsonStructure($this->productListJsonStructure);
});

test('verifies products category filter', function () {
    Product::truncate();
    Product::factory()
        ->has(
            Price::factory([
                'is_active' => true,
            ])->count(4)
        )
        ->count(10)
        ->create();

    $category = \App\Models\Category::inRandomOrder()->first();

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/products/search', [
            'filters' => [
                [
                    'field' => 'category_id',
                    'operator' => '=',
                    'value' => $category->id,
                ]
            ]
        ]);

    foreach ($response->json('data') as $product) {
        expect($category->id == $product['category']['id'])->toBeTrue();
    }

    $response
        ->assertStatus(200)
        ->assertJsonStructure($this->productListJsonStructure);
});

test('verifies products price range filter', function () {
    $minPrice = 50000;
    $maxPrice = 100000;
    $minSearch = 60000;
    $maxSearch = 90000;
    Product::truncate();

    // Crea un producto con un precio dentro del rango de búsqueda
    $productInRange = Product::factory()
        ->has(
            Price::factory([
                'is_active' => true,
                'price' => ($minSearch + $maxSearch) / 2 
            ])->count(1)
        )
        ->create();

    // Crea otros productos con precios aleatorios
    Product::factory()
        ->has(
            Price::factory([
                'is_active' => true,
                'price' => random_int($minPrice, $maxPrice)
            ])->count(4)
        )
        ->count(9)
        ->create();

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/products/search', [
            'filters' => [
                [
                    'field' => 'price',
                    'min' => $minSearch,
                    'max' => $maxSearch,
                    'sort' => 'desc'
                ]
            ]
        ]);

    expect($response->json('data'))->not->toBeEmpty();

    foreach ($response->json('data') as $product) {
        expect($product['price'])->toBeGreaterThanOrEqual($minSearch);
        expect($product['price'])->toBeLessThanOrEqual($maxSearch);
    }

    $response
        ->assertStatus(200)
        ->assertJsonStructure($this->productListJsonStructure);
});

test('verifies that inexistent product is not found', function () {
    Product::truncate();

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/products/1');

    $response->assertStatus(404);
});

test('verifies products subcategory filter', function () {
    Product::truncate();
    $subcategory = \App\Models\Subcategory::factory()->create();

    Product::factory()
        ->has(
            Price::factory([
                'is_active' => true,
            ])->count(4)
        )
        ->count(10)
        ->create(['subcategory_id' => $subcategory->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/products/search', [
            'filters' => [
                [
                    'field' => 'subcategory_id',
                    'operator' => '=',
                    'value' => $subcategory->id,
                ]
            ]
        ]);

    expect($response->json('data'))->not->toBeEmpty();

    foreach ($response->json('data') as $product) {
        expect($subcategory->id == $product['subcategory']['id'])->toBeTrue();
    }

    $response
        ->assertStatus(200)
        ->assertJsonStructure($this->productListJsonStructure);
});

test('verifies products name filter (exact and partial match)', function () {
    Product::truncate();

    // Crea productos con nombres específicos
    $product1 = Product::factory()
        ->has(Price::factory(['is_active' => true])->count(1))
        ->create(['name' => 'Leche condensada']);

    $product2 = Product::factory()
        ->has(Price::factory(['is_active' => true])->count(1))
        ->create(['name' => 'Leche consedada']);

    // Búsqueda por nombre exacto
    $responseExact = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/products/search', [
            'filters' => [
                [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'Leche condensada',
                ]
            ]
        ]);

    expect($responseExact->json('data'))->not->toBeEmpty();
    foreach ($responseExact->json('data') as $product) {
        expect($product['name'])->toBe('Leche condensada');
    }
    $responseExact->assertStatus(200);

    // Búsqueda por nombre parecido (LIKE)
    $responseLike = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/products/search', [
            'filters' => [
                [
                    'field' => 'name',
                    'operator' => 'ILIKE',
                    'value' => '%leche%',
                ]
            ]
        ]);

    expect($responseLike->json('data'))->not->toBeEmpty();
    foreach ($responseLike->json('data') as $product) {
        expect(stripos($product['name'], 'Leche'))->not->toBeFalse();
    }
    $responseLike->assertStatus(200);
});

test('verifies products name filter with fulltext similarity', function () {
    Product::truncate();

    // Crea productos con nombres similares
    $product1 = Product::factory()
        ->has(Price::factory(['is_active' => true])->count(1))
        ->create(['name' => 'Leche condensada']);

    $product2 = Product::factory()
        ->has(Price::factory(['is_active' => true])->count(1))
        ->create(['name' => 'Leche consedada']);

    // Búsqueda por fulltext similarity
    $responseFulltext = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/products/search', [
            'filters' => [
                [
                    'field' => 'name',
                    'operator' => 'fulltext',
                    'value' => 'leche condensada',
                ]
            ]
        ]);

    expect($responseFulltext->json('data'))->not->toBeEmpty();
    foreach ($responseFulltext->json('data') as $product) {
        expect(stripos($product['name'], 'leche'))->not->toBeFalse();
    }
    $responseFulltext->assertStatus(200);
});
