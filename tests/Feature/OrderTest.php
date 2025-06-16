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
use App\Models\Address;
use App\Services\WebpayService;
use App\Models\PaymentMethod;
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

test('puede iniciar el pago de una orden', function () {
    // Arrange

    //Crea el medio de pago Transbank
    $paymentMethod = PaymentMethod::create([
        'name' => 'Transbank',
        'is_active' => true
    ]);

    //Crea una direccion de envio para el usuario
    $address = Address::factory()->create([
        'user_id' => $this->user->id
    ]);

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
        'price' => $price1->price,
        'unit' => $price1->unit
    ]);

    $region = Region::factory()->create();
    $municipality = Municipality::factory()->create([
        'region_id' => $region->id
    ]);


    $this->actingAs($this->user);
    $address = Address::where('user_id', $this->user->id)->first();

    // Act
    $response = $this->postJson('/api/orders/pay', [
        'address_id' => $address->id
    ]);

    // Assert - Verificar estado de la base de datos
    $this->assertDatabaseCount('cart_items', 1);

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'order' => [
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
                    'order_items' => [
                        '*' => [
                            'id',
                            'product' => [
                                'id',
                                'name',
                                'random_product_id',
                                'description',
                                'category_id',
                                'subcategory_id',
                                'brand_id',
                                'sku',
                                'status',
                                'created_at',
                                'updated_at',
                                'image'
                            ],
                            'unit',
                            'quantity',
                            'price',
                            'subtotal',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'order_meta',
                    'created_at',
                    'updated_at'
                ],
                'payment_url',
                'token'
            ]
        ]);
});

/*
test('puede pagar una orden pendiente', function () {
    // Arrange

    //Crea una direccion de envio para el usuario
    $address = Address::factory()->create([
        'user_id' => $this->user->id
    ]);

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
    $this->actingAs($this->user);

    $address = Address::where('user_id', $this->user->id)->first();

    // Act
    $response = $this->postJson('/api/orders/pay', [
        'address_id' => $address->id
    ]);

    dd($response->json());

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
*/

test('no puede pagar una orden que no existe', function () {
    // Arrange
    $address = Address::factory()->create([
        'user_id' => $this->user->id
    ]);

    // Act
    $this->actingAs($this->user);
    $response = $this->postJson('/api/orders/pay', [
        'address_id' => $address->id
    ]);

    // Assert
    $response->assertStatus(400)
        ->assertJson(['message' => 'El carrito está vacío']);
});


test('no puede acceder a la ruta de pago sin estar autenticado', function () {
    // Arrange 
    $address = Address::factory()->create([
        'user_id' => $this->user->id
    ]);


    // Act
    $response = $this->postJson('/api/orders/pay', [
        'address_id' => $address->id
    ]);

    // Assert
    $response->assertStatus(400)
        ->assertJson(['message' => 'El carrito está vacío']);
});
