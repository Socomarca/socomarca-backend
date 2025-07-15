<?php

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

// Test exportación de categorías
test('puede exportar categorías a excel', function () {
    Excel::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Category::factory()->count(3)->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->get('/api/exports/categories');

    $response->assertStatus(200);

});

// Test exportación de clientes
test('puede exportar clientes a excel', function () {
    Excel::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $clientes = User::factory()->count(3)->create();
    foreach ($clientes as $cliente) {
        $cliente->assignRole('cliente');
    }

    $response = $this->actingAs($admin, 'sanctum')
        ->get('/api/exports/users');

    $response->assertStatus(200);

});

// Test que no permite exportar categorías si el usuario no tiene rol permitido
test('no puede exportar categorías si no tiene rol permitido', function () {
    Excel::fake();

    $user = User::factory()->create(); // Sin rol admin/superadmin/supervisor

    $response = $this->actingAs($user, 'sanctum')
        ->get('/api/exports/categories');

    $response->assertStatus(403);
});