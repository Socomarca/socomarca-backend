<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Order;
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
        ->get('/api/categories/exports');

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
        ->get('/api/users/exports');

    $response->assertStatus(200);

});

// Test que no permite exportar categorías si el usuario no tiene rol permitido
test('no puede exportar categorías si no tiene rol permitido', function () {
    Excel::fake();

    $user = User::factory()->create(); // Sin rol admin/superadmin/supervisor

    $response = $this->actingAs($user, 'sanctum')
        ->get('/api/categories/exports');

    $response->assertStatus(403);
});

test('puede exportar transacciones exitosas a excel', function () {
    Excel::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Crea órdenes exitosas
    Order::factory()->count(2)->create(['status' => 'completed']);
    Order::factory()->count(1)->create(['status' => 'failed']);

    $response = $this->actingAs($admin, 'sanctum')
        ->get('/api/orders/exports/transactions?status=completed');

    $response->assertStatus(200);


});

test('puede exportar transacciones fallidas a excel', function () {
    Excel::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Crea órdenes fallidas
    Order::factory()->count(2)->create(['status' => 'failed']);
    Order::factory()->count(1)->create(['status' => 'completed']);

    $response = $this->actingAs($admin, 'sanctum')
        ->get('/api/orders/exports/transactions?status=failed');

    $response->assertStatus(200);

    
});