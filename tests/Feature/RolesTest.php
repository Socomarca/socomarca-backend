<?php
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'superadmin']);
    Role::firstOrCreate(['name' => 'cliente']);
    Role::firstOrCreate(['name' => 'supervisor']);
    Role::firstOrCreate(['name' => 'editor']);
    
    // Crear algunos permisos para testing
    Permission::firstOrCreate(['name' => 'see-all-reports']);
    Permission::firstOrCreate(['name' => 'manage-users']);
    Permission::firstOrCreate(['name' => 'see-own-purchases']);
});

test('admin and superadmin can list roles and permissions', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $superadmin = User::factory()->create();
    $superadmin->assignRole('superadmin');

    $route = '/api/roles/users'; 

    // Admin puede acceder
    $this->actingAs($admin, 'sanctum')
        ->getJson($route)
        ->assertStatus(200);

    // Superadmin puede acceder
    $this->actingAs($superadmin, 'sanctum')
        ->getJson($route)
        ->assertStatus(200);
});

test('other users cannot list roles and permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('cliente'); 

    $route = '/api/roles/users';

    $this->actingAs($user, 'sanctum')
        ->getJson($route)
        ->assertStatus(403); 
});

test('admin can get all roles without pagination', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/roles');

    $response->assertStatus(200)
        ->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'permissions',
                'created_at',
                'updated_at'
            ]
        ]);

    // Verificar que devuelve al menos los roles básicos
    $data = $response->json();
    $roleNames = array_column($data, 'name');
    
    expect($roleNames)->toContain('admin');
    expect($roleNames)->toContain('cliente');
    expect($roleNames)->toContain('superadmin');
});

test('superadmin can get all roles without pagination', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('superadmin');

    $response = $this->actingAs($superadmin, 'sanctum')
        ->getJson('/api/roles');

    $response->assertStatus(200)
        ->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'permissions',
                'created_at',
                'updated_at'
            ]
        ]);

    // Verificar que cada rol tiene la estructura correcta
    $data = $response->json();
    foreach ($data as $role) {
        expect($role)->toHaveKeys(['id', 'name', 'permissions', 'created_at', 'updated_at']);
        expect($role['permissions'])->toBeArray();
    }
});

test('non-admin users cannot get roles list', function () {
    $user = User::factory()->create();
    $user->assignRole('cliente');

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/roles');

    $response->assertStatus(403);
});

test('supervisor cannot get roles list', function () {
    $supervisor = User::factory()->create();
    $supervisor->assignRole('supervisor');

    $response = $this->actingAs($supervisor, 'sanctum')
        ->getJson('/api/roles');

    $response->assertStatus(403);
});

test('editor cannot get roles list', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $response = $this->actingAs($editor, 'sanctum')
        ->getJson('/api/roles');

    $response->assertStatus(403);
});

test('unauthenticated users cannot get roles list', function () {
    $response = $this->getJson('/api/roles');

    $response->assertStatus(401);
});

test('roles endpoint returns correct data structure', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Asignar algunos permisos a un rol para verificar la estructura
    $role = Role::findByName('admin');
    $role->givePermissionTo(['see-all-reports', 'manage-users']);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/roles');

    $response->assertStatus(200);

    $data = $response->json();
    
    // Verificar que es un array
    expect($data)->toBeArray();
    
    // Buscar el rol admin en la respuesta
    $adminRole = collect($data)->firstWhere('name', 'admin');
    
    expect($adminRole)->not->toBeNull();
    expect($adminRole['id'])->toBeInt();
    expect($adminRole['name'])->toBe('admin');
    expect($adminRole['permissions'])->toBeArray();
    expect($adminRole['permissions'])->toContain('see-all-reports');
    expect($adminRole['permissions'])->toContain('manage-users');
    expect($adminRole['created_at'])->toBeString();
    expect($adminRole['updated_at'])->toBeString();
});