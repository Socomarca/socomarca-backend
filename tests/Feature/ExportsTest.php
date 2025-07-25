<?php

use App\Exports\OrdersExport;
use App\Models\User;
use App\Models\Category;
use App\Models\Municipality;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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

    // Prepara un usuario admin
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Crea 2 órdenes exitosas y 1 fallida
    Order::factory()->count(2)->create(['status' => 'completed']);
    Order::factory()->count(1)->create(['status' => 'failed']);

    // Ejecuta la exportación autenticado como admin
    $response = $this->actingAs($admin, 'sanctum')
        ->post('/api/orders/reports/transactions/export', ['filename' => 'export.xlsx']);

    $response->assertStatus(200);

    Excel::assertDownloaded('export.xlsx', function ($export) {
                
        expect($export)->toBeInstanceOf(OrdersExport::class);

        $collection = $export->collection();
        expect($collection)->toHaveCount(2);
        foreach ($collection as $order) {
            expect($order['Estado'] ?? $order->status)->toBe('completed');
        }

        return true;
    });
});

test('puede exportar transacciones fallidas a excel', function () {
    Excel::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Order::factory()->count(2)->create(['status' => 'failed']);
    Order::factory()->count(1)->create(['status' => 'completed']);

    $response = $this->actingAs($admin, 'sanctum')
        ->post('/api/orders/reports/transactions/export', [
            'status' => 'failed',
            'filename' => 'export.xlsx'
        ]);

    $response->assertStatus(200);

    Excel::assertDownloaded('export.xlsx', function ($export) {
        expect($export)->toBeInstanceOf(OrdersExport::class);
        $collection = $export->collection();
        expect($collection)->toHaveCount(2);
        foreach ($collection as $order) {
            expect($order['Estado'] ?? $order->status)->toBe('failed');
        }
        return true;
    });
});

test('puede exportar top comunas por ventas a excel', function () {
    Excel::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $municipality1 = Municipality::factory()->create(['name' => 'Comuna Uno']);
    $municipality2 = Municipality::factory()->create(['name' => 'Comuna Dos']);

    // Crea órdenes en diferentes meses y comunas
    Order::factory()->create([
        'status' => 'completed',
        'amount' => 10000,
        'created_at' => now()->startOfMonth(),
        'order_meta' => ['address' => ['municipality_id' => $municipality1->id]],
    ]);
    Order::factory()->create([
        'status' => 'completed',
        'amount' => 20000,
        'created_at' => now()->startOfMonth(),
        'order_meta' => ['address' => ['municipality_id' => $municipality2->id]],
    ]);
    Order::factory()->create([
        'status' => 'completed',
        'amount' => 30000,
        'created_at' => now()->subMonth()->startOfMonth(),
        'order_meta' => ['address' => ['municipality_id' => $municipality1->id]],
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->post('api/orders/reports/municipalities/export', [
            'filename' => 'export.xlsx'
        ]);

    $response->assertStatus(200);

    Excel::assertDownloaded('export.xlsx', function ($export) {
        expect($export)->toBeInstanceOf(\App\Exports\TopMunicipalitiesExport::class);
        $collection = $export->collection();
        expect($collection)->not()->toBeEmpty();
        return true;
    });
});

test('puede exportar el producto más vendido por mes a excel', function () {
    Excel::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $productA = Product::factory()->create(['name' => 'Producto A']);
    $productB = Product::factory()->create(['name' => 'Producto B']);

    // Mes actual: Producto A vende más
    $order1 = Order::factory()->create(['status' => 'completed', 'created_at' => now()->startOfMonth()]);
    OrderItem::factory()->create(['order_id' => $order1->id, 'product_id' => $productA->id, 'quantity' => 10, 'price' => 1000]);
    OrderItem::factory()->create(['order_id' => $order1->id, 'product_id' => $productB->id, 'quantity' => 5, 'price' => 1000]);

    // Mes anterior: Producto B vende más
    $order2 = Order::factory()->create(['status' => 'completed', 'created_at' => now()->subMonth()->startOfMonth()]);
    OrderItem::factory()->create(['order_id' => $order2->id, 'product_id' => $productA->id, 'quantity' => 3, 'price' => 1000]);
    OrderItem::factory()->create(['order_id' => $order2->id, 'product_id' => $productB->id, 'quantity' => 8, 'price' => 1000]);

    $response = $this->actingAs($admin, 'sanctum')
        ->post('/api/orders/reports/products/export', [
            'filename' => 'export.xlsx'
        ]);

    $response->assertStatus(200);

    Excel::assertDownloaded('export.xlsx', function ($export) {
        expect($export)->toBeInstanceOf(\App\Exports\TopProductsExport::class);
        $collection = $export->collection();
        expect($collection)->not()->toBeEmpty();
        return true;
    });
});
