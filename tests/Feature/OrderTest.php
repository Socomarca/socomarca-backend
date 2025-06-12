<?php

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Brand;
use App\Models\CartItem;
use App\Models\Price;
use App\Models\Region;
use App\Models\Municipality;
use App\Services\WebpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('puede listar las órdenes del usuario', function () {
    // Arrange
    Order::factory()->count(3)->create([
        'user_id' => $this->user->id
    ]);

    Order::factory()->create([
        'user_id' => User::factory()->create()->id
    ]);

    // Act
    $response = $this->getJson('/api/orders?user_id=' . $this->user->id);

    // Assert
    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'email_verified_at',
                        'phone',
                        'rut',
                        'business_name',
                        'is_active',
                        'last_login',
                        'password_changed_at',
                        'created_at',
                        'updated_at'
                    ],
                    'subtotal',
                    'amount',
                    'status',
                    'order_items',
                    'created_at',
                    'updated_at'
                ]
            ]
        ]);
});

test('puede crear una orden desde el carrito', function () {
    // Arrange
    // Crear datos necesarios para los productos
    $category = Category::factory()->create();
    $subcategory = Subcategory::factory()->create([
        'category_id' => $category->id
    ]);
    $brand = Brand::factory()->create();

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'brand_id' => $brand->id
    ]);

    // Crear productos con sus precios
    $price1 = Price::factory()->create([
        'product_id' => $product->id,
        'price_list_id' => fake()->word(),
        'unit' => 'kg',
        'price' => 100,
        'valid_from' => now()->subDays(1),
        'valid_to' => null,
        'is_active' => true
    ]);

    CartItem::create([
        'user_id' => $this->user->id,
        'product_id' => $price1->product_id,
        'quantity' => 2,
        'price' => $price1->price
    ]);

    $region = Region::factory()->create();
    $municipality = Municipality::factory()->create([
        'region_id' => $region->id
    ]);

    // Act
    $response = $this->postJson('/api/orders/create-from-cart', [
        'user_id' => $this->user->id,
        "name" => fake()->name(),
        "rut" => fake()->numerify('########-#'),
        "email" => fake()->email(),
        "phone" => fake()->phoneNumber(),
        "address" => fake()->address(),
        "region_id" => $region->id,
        "municipality_id" => $municipality->id,
        "billing_address" => fake()->address()
    ]);
    
    // Assert - Verificar estado de la base de datos
    $this->assertDatabaseCount('cart_items', 0);

    // Assert
    $response->assertStatus(201);
});

test('no puede crear una orden con carrito vacío', function () {
    // Arrange 
    $region = Region::factory()->create();
    $municipality = Municipality::factory()->create([
        'region_id' => $region->id
    ]);
    // Act
    $response = $this->postJson('/api/orders/create-from-cart', [
        'user_id' => $this->user->id,
        "name" => fake()->name(),
        "rut" => fake()->numerify('########-#'),
        "email" => fake()->email(),
        "phone" => fake()->phoneNumber(),
        "address" => fake()->address(),
        "region_id" => $region->id,
        "municipality_id" => $municipality->id,
        "billing_address" => fake()->address()
    ]);

    // Assert
    $response->assertStatus(400)
        ->assertJson(['message' => 'El carrito está vacío']);
});

test('puede pagar una orden pendiente', function () {
    // Arrange
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'pending'
    ]);


    //Mock del servicio de webpay, no se si deberia ir directamente al servicio de webpay
    $this->mock(WebpayService::class, function ($mock) {
        $mock->shouldReceive('createTransaction')
            ->once()
            ->andReturn([
                'url' => 'https://webpay.test/init',
                'token' => 'test-token'
            ]);
    });

    // Act
    $response = $this->postJson('/api/orders/pay', [
        'user_id' => $this->user->id,
        'order_id' => $order->id
    ]);

    // Assert
    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'order',
                'payment_url',
                'token'
            ]
        ]);
});

test('no puede pagar una orden que no existe', function () {
    // Arrange - No se necesita configuración adicional

    // Act
    $response = $this->postJson('/api/orders/pay', [
        'user_id' => $this->user->id,
        'order_id' => 999
    ]);

    // Assert
    $response->assertStatus(422)
        ->assertJson(['message' => 'La orden no existe']);
});

test('no puede pagar una orden de otro usuario', function () {
    // Arrange
    $otherUser = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $otherUser->id,
        'status' => 'pending'
    ]);

    // Act
    $response = $this->postJson('/api/orders/pay', [
        'user_id' => $this->user->id,
        'order_id' => $order->id
    ]);

    // Assert
    $response->assertStatus(403)
        ->assertJson(['message' => 'No tienes permiso para pagar esta orden']);
});

test('no puede pagar una orden que no está pendiente', function () {
    // Arrange
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'completed'
    ]);

    // Act
    $response = $this->postJson('/api/orders/pay', [
        'user_id' => $this->user->id,
        'order_id' => $order->id
    ]);

    // Assert
    $response->assertStatus(400)
        ->assertJson(['message' => 'La orden no está pendiente de pago']);
});

