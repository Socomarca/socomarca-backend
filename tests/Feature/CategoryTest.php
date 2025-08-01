<?php

use App\Models\Category;

beforeEach(function ()
{
    $this->user = createUser();
    $this->category = createCategory();

    $this->categoryListJsonStructure = [
        'data' => [
            [
                'id',
                'name',
                'description',
                'code',
                'level',
                'key',
                'subcategories_count',
                'products_count',
                'created_at',
                'updated_at',
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

test('requiere autenticacion con token', function ()
{
    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/categories');

    $response->assertStatus(401);
});

test('retorna categorias con estructura correcta', function ()
{
    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/categories');

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                    'description',
                    'code',
                    'level',
                    'key',
                    'subcategories_count',
                    'products_count',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});

test('retorna 404 para categoria inexistente', function ()
{
    $id = $this->category->id;

    Category::truncate();

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/categories/' . $id);

    $response->assertStatus(404);
});

test('requiere autenticacion para busqueda de categorias', function () {
    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->postJson('/api/categories/search');

    $response->assertStatus(401);
});

test('retorna estructura correcta para busqueda de categorias', function () {
    Category::truncate();
    Category::factory()->count(5)->create();

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->postJson('/api/categories/search');

    expect($response->json('data'))->toHaveCount(5);
    $response
        ->assertStatus(200)
        ->assertJsonStructure($this->categoryListJsonStructure);
});

test('filtra categorias por nombre exacto', function () {
    Category::truncate();
    
    Category::factory()->create(['name' => 'Lácteos y Derivados']);
    Category::factory()->create(['name' => 'Bebidas']);
    Category::factory()->create(['name' => 'Carnes y Pescados']);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/categories/search', [
            'filters' => [
                [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'Lácteos y Derivados',
                ]
            ]
        ]);

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data')[0]['name'])->toBe('Lácteos y Derivados');
    $response->assertStatus(200);
});

test('filtra categorias por nombre parcial', function () {
    Category::truncate();
    
    Category::factory()->create(['name' => 'Productos Lácteos']);
    Category::factory()->create(['name' => 'Lácteos Sin Lactosa']);
    Category::factory()->create(['name' => 'Bebidas']);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/categories/search', [
            'filters' => [
                [
                    'field' => 'name',
                    'operator' => 'ILIKE',
                    'value' => '%lácteos%',
                ]
            ]
        ]);

    expect($response->json('data'))->toHaveCount(2);
    foreach ($response->json('data') as $category) {
        expect(stripos($category['name'], 'lácteos'))->not->toBeFalse();
    }
    $response->assertStatus(200);
});

test('filtra categorias por descripcion', function () {
    Category::truncate();
    
    Category::factory()->create(['name' => 'Lácteos', 'description' => 'Productos lácteos y derivados']);
    Category::factory()->create(['name' => 'Bebidas', 'description' => 'Bebidas sin alcohol']);
    Category::factory()->create(['name' => 'Carnes', 'description' => 'Carnes rojas y blancas']);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/categories/search', [
            'filters' => [
                [
                    'field' => 'description',
                    'operator' => 'ILIKE',
                    'value' => '%lácteos%',
                ]
            ]
        ]);

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data')[0]['description'])->toContain('lácteos');
    $response->assertStatus(200);
});

test('ordena categorias por nombre', function () {
    Category::truncate();
    
    Category::factory()->create(['name' => 'Zebra']);
    Category::factory()->create(['name' => 'Alfa']);
    Category::factory()->create(['name' => 'Beta']);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/categories/search', [
            'filters' => [
                [
                    'field' => 'name',
                    'operator' => 'ILIKE',
                    'value' => '%',
                    'sort' => 'ASC'
                ]
            ]
        ]);

    $data = $response->json('data');
    expect($data)->toHaveCount(3);
    expect($data[0]['name'])->toBe('Alfa');
    expect($data[1]['name'])->toBe('Beta');
    expect($data[2]['name'])->toBe('Zebra');
    $response->assertStatus(200);
});

test('filtra categorias por nivel', function () {
    Category::truncate();
    
    Category::factory()->create(['level' => 1]);
    Category::factory()->create(['level' => 2]);
    Category::factory()->create(['level' => 1]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/categories/search', [
            'filters' => [
                [
                    'field' => 'level',
                    'operator' => '=',
                    'value' => 1,
                ]
            ]
        ]);

    expect($response->json('data'))->toHaveCount(2);
    foreach ($response->json('data') as $category) {
        expect($category['level'])->toBe(1);
    }
    $response->assertStatus(200);
});

test('ordena categorias por id ascendente y descendente', function () {
    Category::truncate();

    $catA = Category::factory()->create(['name' => 'Primera']);
    $catB = Category::factory()->create(['name' => 'Segunda']);
    $catC = Category::factory()->create(['name' => 'Tercera']);

    // Orden ascendente
    $responseAsc = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/categories/search', [
            'sort' => 'id',
            'sort_direction' => 'asc'
        ]);
    $idsAsc = array_column($responseAsc->json('data'), 'id');
    expect($idsAsc)->toBe([min($catA->id, $catB->id, $catC->id), ...array_diff([$catA->id, $catB->id, $catC->id], [min($catA->id, $catB->id, $catC->id), max($catA->id, $catB->id, $catC->id)]), max($catA->id, $catB->id, $catC->id)]);

    // Orden descendente
    $responseDesc = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/categories/search', [
            'sort' => 'id',
            'sort_direction' => 'desc'
        ]);
    $idsDesc = array_column($responseDesc->json('data'), 'id');
    expect($idsDesc)->toBe([max($catA->id, $catB->id, $catC->id), ...array_diff([$catA->id, $catB->id, $catC->id], [min($catA->id, $catB->id, $catC->id), max($catA->id, $catB->id, $catC->id)]), min($catA->id, $catB->id, $catC->id)]);
});

test('filtra y ordena categorias por nombre y id', function () {
    Category::truncate();

    $catA = Category::factory()->create(['name' => 'Alimentos']);
    $catB = Category::factory()->create(['name' => 'Bebidas']);
    $catC = Category::factory()->create(['name' => 'Carnes']);

    // Filtrar por nombre parcial y ordenar por id descendente
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/categories/search', [
            'filters' => [
                [
                    'field' => 'name',
                    'operator' => 'ILIKE',
                    'value' => '%a%',
                ]
            ],
            'sort' => 'id',
            'sort_direction' => 'desc'
        ]);

    $data = $response->json('data');
    // Debe traer solo las categorías cuyo nombre contiene "a" (no sensible a mayúsculas)
    $expected = collect([$catA, $catB, $catC])
        ->filter(fn($cat) => stripos($cat->name, 'a') !== false)
        ->sortByDesc('id')
        ->pluck('id')
        ->values()
        ->all();

    $ids = array_column($data, 'id');
    expect($ids)->toBe($expected);
    $response->assertStatus(200);
});