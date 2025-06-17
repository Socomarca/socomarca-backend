<?php
use App\Models\User;

beforeEach(function () {
    
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'cliente']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'supervisor']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'editor']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'superadmin']);
});

test('puede buscar usuarios agrupados por rol y ordenados por nombre', function () {
    $admin = User::factory()->create(['name' => 'Ana Admin']);
    $admin->assignRole('admin');

    $cliente = User::factory()->create(['name' => 'Carlos Cliente']);
    $cliente->assignRole('cliente');

    $supervisor = User::factory()->create(['name' => 'Bea Supervisor']);
    $supervisor->assignRole('supervisor');

    $editor = User::factory()->create(['name' => 'Dani Editor']);
    $editor->assignRole('editor');

    $superadmin = User::factory()->create(['name' => 'Zoe Superadmin']);
    $superadmin->assignRole('superadmin');

    $adminUser = User::factory()->create();
    $adminUser->assignRole('admin');

    $route = '/api/users/search';

    $payload = [
        'roles' => ['admin', 'cliente', 'supervisor', 'editor', 'superadmin'],
        'sort_field' => 'name',
        'sort_direction' => 'asc'
    ];

    $this->actingAs($admin, 'sanctum')
        ->postJson($route, $payload)
        ->assertStatus(200)
        ->assertJsonFragment(['role' => 'admin'])
        ->assertJsonFragment(['role' => 'cliente'])
        ->assertJsonFragment(['role' => 'supervisor'])
        ->assertJsonFragment(['role' => 'editor'])
        ->assertJsonFragment(['role' => 'superadmin']);
});

test('valida que los roles sean válidos', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $route = '/api/users/search';

    $payload = [
        'roles' => ['noexiste', 'admin'],
    ];

    $this->actingAs($admin, 'sanctum')
        ->postJson($route, $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['roles.0']);
});

test('valida que sort_field y sort_direction sean válidos', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $route = '/api/users/search';

    $payload = [
        'sort_field' => 'noexiste',
        'sort_direction' => 'up',
    ];

    $this->actingAs($admin, 'sanctum')
        ->postJson($route, $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['sort_field', 'sort_direction']);
});