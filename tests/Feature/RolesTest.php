<?php
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'superadmin']);
    Role::firstOrCreate(['name' => 'cliente']);
    Role::firstOrCreate(['name' => 'supervisor']);
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