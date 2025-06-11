<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Price;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Limpiar y crear permisos
    Permission::firstOrCreate(['name' => 'can-see-all-products']);
    Permission::firstOrCreate(['name' => 'can-edit-products']);
    
    // Crear roles
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $superAdminRole = Role::firstOrCreate(['name' => 'superadmin']);
    $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
    $editorRole = Role::firstOrCreate(['name' => 'editor']);
    
    // Asignar permisos a roles (según el seeder)
    $adminRole->syncPermissions(['can-see-all-products', 'can-edit-products']);
    $superAdminRole->syncPermissions(['can-see-all-products', 'can-edit-products']);
    $supervisorRole->syncPermissions(['can-see-all-products']);
    $editorRole->syncPermissions(['can-see-all-products']); // Editor SÍ tiene permisos según seeder
    
    // Crear usuarios con diferentes roles
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('admin');
    
    $this->superAdminUser = User::factory()->create();
    $this->superAdminUser->assignRole('superadmin');
    
    $this->supervisorUser = User::factory()->create();
    $this->supervisorUser->assignRole('supervisor');
    
    $this->editorUser = User::factory()->create();
    $this->editorUser->assignRole('editor');
    
    $this->userWithoutRole = User::factory()->create();
    
    // Crear productos de prueba
    Product::factory()
        ->has(Price::factory(['is_active' => true])->count(1))
        ->count(5)
        ->create();
});

describe('Permisos de Productos - Usuarios sin autenticar', function () {
    test('usuario no autenticado no puede listar productos', function () {
        $response = $this->getJson('/api/products');
        
        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    });
    
    test('usuario no autenticado no puede ver producto específico', function () {
        $product = Product::first();
        
        $response = $this->getJson("/api/products/{$product->id}");
        
        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    });
    
    test('usuario no autenticado no puede buscar productos', function () {
        $response = $this->postJson('/api/products/search', [
            'filters' => [],
            'per_page' => 10
        ]);
        
        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    });
});

describe('Permisos de Productos - Usuario Administrador', function () {
    test('administrador puede listar productos', function () {
        $response = $this->actingAs($this->adminUser, 'sanctum')
                        ->getJson('/api/products');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
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
                            'category',
                            'subcategory',
                            'brand'
                        ]
                    ],
                    'meta'
                ]);
    });
    
    test('administrador puede ver producto específico', function () {
        $product = Product::first();
        
        $response = $this->actingAs($this->adminUser, 'sanctum')
                        ->getJson("/api/products/{$product->id}");
        $response->assertStatus(200);
    });
    
    test('administrador puede buscar productos', function () {
        $response = $this->actingAs($this->adminUser, 'sanctum')
                        ->postJson('/api/products/search', [
                            'filters' => [],
                            'per_page' => 10
                        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
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
                            'category',
                            'subcategory',
                            'brand'
                        ]
                    ],
                    'meta'
                ]);
    });
});

describe('Permisos de Productos - Usuario SuperAdministrador', function () {
    test('superadministrador puede listar productos', function () {
        $response = $this->actingAs($this->superAdminUser, 'sanctum')
                        ->getJson('/api/products');
        
        $response->assertStatus(200);
    });
    
    test('superadministrador puede ver producto específico', function () {
        $product = Product::first();
        
        $response = $this->actingAs($this->superAdminUser, 'sanctum')
                        ->getJson("/api/products/{$product->id}");
        
        $response->assertStatus(200);
    });
    
    test('superadministrador puede buscar productos', function () {
        $response = $this->actingAs($this->superAdminUser, 'sanctum')
                        ->postJson('/api/products/search', [
                            'filters' => [],
                            'per_page' => 10
                        ]);
        
        $response->assertStatus(200);
    });
});

describe('Permisos de Productos - Usuario Supervisor', function () {
    test('supervisor puede listar productos', function () {
        $response = $this->actingAs($this->supervisorUser, 'sanctum')
                        ->getJson('/api/products');
        
        $response->assertStatus(200);
    });
    
    test('supervisor puede ver producto específico', function () {
        $product = Product::first();
        
        $response = $this->actingAs($this->supervisorUser, 'sanctum')
                        ->getJson("/api/products/{$product->id}");
        
        $response->assertStatus(200);
    });
    
    test('supervisor puede buscar productos', function () {
        $response = $this->actingAs($this->supervisorUser, 'sanctum')
                        ->postJson('/api/products/search', [
                            'filters' => [],
                            'per_page' => 10
                        ]);
        
        $response->assertStatus(200);
    });
});

describe('Permisos de Productos - Usuario Editor (Con Permisos)', function () {
    test('editor puede listar productos', function () {
        $response = $this->actingAs($this->editorUser, 'sanctum')
                        ->getJson('/api/products');
        
        $response->assertStatus(200);
    });
    
    test('editor puede ver producto específico', function () {
        $product = Product::first();
        
        $response = $this->actingAs($this->editorUser, 'sanctum')
                        ->getJson("/api/products/{$product->id}");
        
        $response->assertStatus(200);
    });
    
    test('editor puede buscar productos', function () {
        $response = $this->actingAs($this->editorUser, 'sanctum')
                        ->postJson('/api/products/search', [
                            'filters' => [],
                            'per_page' => 10
                        ]);
        
        $response->assertStatus(200);
    });
});

describe('Permisos de Productos - Usuario sin Rol', function () {
    test('usuario sin rol no puede listar productos', function () {
        $response = $this->actingAs($this->userWithoutRole, 'sanctum')
                        ->getJson('/api/products');
        
        $response->assertStatus(403)
                ->assertJson([
                    'message' => 'No tienes permisos para realizar esta acción.',
                    'required_permission' => 'can-see-all-products'
                ]);
    });
    
    test('usuario sin rol no puede ver producto específico', function () {
        $product = Product::first();
        
        $response = $this->actingAs($this->userWithoutRole, 'sanctum')
                        ->getJson("/api/products/{$product->id}");
        
        $response->assertStatus(403)
                ->assertJson([
                    'message' => 'No tienes permisos para realizar esta acción.',
                    'required_permission' => 'can-see-all-products'
                ]);
    });
    
    test('usuario sin rol no puede buscar productos', function () {
        $response = $this->actingAs($this->userWithoutRole, 'sanctum')
                        ->postJson('/api/products/search', [
                            'filters' => [],
                            'per_page' => 10
                        ]);
        
        $response->assertStatus(403)
                ->assertJson([
                    'message' => 'No tienes permisos para realizar esta acción.',
                    'required_permission' => 'can-see-all-products'
                ]);
    });
});

describe('Casos de Borde - Productos', function () {
    test('administrador intentando acceder a producto inexistente', function () {
        $response = $this->actingAs($this->adminUser, 'sanctum')
                        ->getJson('/api/products/999999');
        
        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Product not found.'
                ]);
    });
    
    test('verifica que el middleware funciona correctamente con filtros complejos', function () {
        $response = $this->actingAs($this->supervisorUser, 'sanctum')
                        ->postJson('/api/products/search', [
                            'filters' => [
                                [
                                    'field' => 'price',
                                    'min' => 1000,
                                    'max' => 50000,
                                    'sort' => 'desc'
                                ]
                            ],
                            'per_page' => 5
                        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'meta',
                    'filters' => [
                        'min_price',
                        'max_price'
                    ]
                ]);
    });
    
    test('verifica respuesta con token inválido', function () {
        $response = $this->withHeaders([
                            'Authorization' => 'Bearer token-invalido',
                            'Accept' => 'application/json'
                        ])
                        ->getJson('/api/products');
        
        $response->assertStatus(401);
    });
}); 