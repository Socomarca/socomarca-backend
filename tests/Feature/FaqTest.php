<?php

use App\Models\Faq;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Crear roles si no existen
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'superadmin']);
    
    $this->user = createUser();
    $this->admin = User::factory()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);
    $this->admin->assignRole('admin');
    
    $this->faq = Faq::factory()->create();

    $this->faqJsonStructure = [
        'id',
        'question',
        'answer',
        'created_at',
        'updated_at',
    ];

    $this->faqResourceStructure = [
        'data' => [
            'id',
            'question',
            'answer',
            'created_at',
            'updated_at',
        ]
    ];

    $this->faqListJsonStructure = [
        'data' => [
            $this->faqJsonStructure
        ],
    ];
});

// Tests públicos
test('lista faqs sin autenticacion', function () {
    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/faq');

    $response
        ->assertStatus(200)
        ->assertJsonStructure($this->faqListJsonStructure);
});

test('busca faqs sin autenticacion', function () {
    Faq::truncate();
    Faq::factory()->create([
        'question' => '¿Cómo realizar un pedido específico?',
        'answer' => 'Para realizar un pedido específico, debes...'
    ]);

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->postJson('/api/faq/search', [
            'search' => 'específico'
        ]);

    $response
        ->assertStatus(200)
        ->assertJsonStructure($this->faqListJsonStructure);
    
    expect($response->json('data'))->toHaveCount(1);
});

test('busca faqs con filtros', function () {
    Faq::truncate();
    Faq::factory()->create([
        'question' => '¿Cómo realizar un pedido?',
        'answer' => 'Para realizar un pedido...'
    ]);
    Faq::factory()->create([
        'question' => '¿Cuánto demora el envío?',
        'answer' => 'El envío demora...'
    ]);

    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->postJson('/api/faq/search', [
            'filters' => [
                [
                    'field' => 'question',
                    'operator' => 'ILIKE',
                    'value' => '%pedido%'
                ]
            ]
        ]);

    $response
        ->assertStatus(200)
        ->assertJsonStructure($this->faqListJsonStructure);
    
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data')[0]['question'])
        ->toContain('pedido');
});

// Tests de administrador
test('admin puede crear faq', function () {
    $faqData = [
        'question' => '¿Nueva pregunta frecuente?',
        'answer' => 'Esta es la respuesta a la nueva pregunta frecuente.'
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->postJson('/api/faq', $faqData);

    $response
        ->assertStatus(201)
        ->assertJsonStructure($this->faqResourceStructure);

    $this->assertDatabaseHas('faqs', $faqData);
});

test('usuario normal no puede crear faq', function () {
    $faqData = [
        'question' => '¿Nueva pregunta frecuente?',
        'answer' => 'Esta es la respuesta a la nueva pregunta frecuente.'
    ];

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->postJson('/api/faq', $faqData);

    $response->assertStatus(403);
});

test('admin puede actualizar faq', function () {
    $updateData = [
        'question' => 'Pregunta actualizada',
        'answer' => 'Respuesta actualizada'
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->putJson("/api/faq/{$this->faq->id}", $updateData);

    $response
        ->assertStatus(200)
        ->assertJsonStructure($this->faqResourceStructure);

    $this->assertDatabaseHas('faqs', [
        'id' => $this->faq->id,
        'question' => 'Pregunta actualizada',
        'answer' => 'Respuesta actualizada'
    ]);
});

test('usuario normal no puede actualizar faq', function () {
    $updateData = [
        'question' => 'Pregunta actualizada',
        'answer' => 'Respuesta actualizada'
    ];

    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->putJson("/api/faq/{$this->faq->id}", $updateData);

    $response->assertStatus(403);
});

test('admin puede eliminar faq', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->deleteJson("/api/faq/{$this->faq->id}");

    $response
        ->assertStatus(200)
        ->assertJson(['message' => 'FAQ eliminada exitosamente.']);

    $this->assertDatabaseMissing('faqs', ['id' => $this->faq->id]);
});

test('usuario normal no puede eliminar faq', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->deleteJson("/api/faq/{$this->faq->id}");

    $response->assertStatus(403);
});

// Validaciones
test('validacion de campos requeridos al crear faq', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->postJson('/api/faq', []);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['question', 'answer']);
});

test('validacion de longitud minima de campos', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->postJson('/api/faq', [
            'question' => 'abc',
            'answer' => 'def'
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['question', 'answer']);
});

test('validacion de longitud maxima de campos', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->postJson('/api/faq', [
            'question' => str_repeat('a', 1001),
            'answer' => str_repeat('b', 5001)
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['question', 'answer']);
});

test('puede mostrar faq individual', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get("/api/faq/{$this->faq->id}");

    $response
        ->assertStatus(200)
        ->assertJsonStructure($this->faqResourceStructure)
        ->assertJson([
            'data' => [
                'id' => $this->faq->id,
                'question' => $this->faq->question,
                'answer' => $this->faq->answer,
            ]
        ]);
});

test('retorna 404 para faq inexistente', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/faq/999999');

    $response->assertStatus(404);
});

test('busqueda por texto completo en pregunta y respuesta', function () {
    Faq::truncate();
    Faq::factory()->create([
        'question' => '¿Cómo realizar un pedido?',
        'answer' => 'Para realizar un pedido necesitas registrarte'
    ]);
    Faq::factory()->create([
        'question' => '¿Cuánto demora el envío?',
        'answer' => 'El envío tiene un tiempo de 2-3 días'
    ]);

    // Buscar por término en pregunta
    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->postJson('/api/faq/search', [
            'search' => 'pedido'
        ]);

    expect($response->json('data'))->toHaveCount(1);

    // Buscar por término en respuesta
    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->postJson('/api/faq/search', [
            'search' => 'envío'
        ]);

    expect($response->json('data'))->toHaveCount(1);
});


