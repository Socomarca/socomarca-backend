<?php
use App\Models\User;
use App\Models\Siteinfo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->admin = $admin;
});

test('un admin puede ver la configuración de precios por cantidad', function () {
    Siteinfo::updateOrCreate(
        ['key' => 'prices_settings'],
        ['value' => ['min_max_quantity_enabled' => true]]
    );

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/settings/prices');

    $response->assertStatus(200);
});

test('un admin puede actualizar la configuración de precios por cantidad', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson('/api/settings/prices', [
            'min_max_quantity_enabled' => false,
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('siteinfo', [
        'key' => 'prices_settings',
    ]);
    $this->assertTrue(Siteinfo::where('key', 'prices_settings')->first()->value['min_max_quantity_enabled'] === false);
});

test('un usuario sin rol admin o superadmin no puede acceder a la configuración', function () {
    $user = User::factory()->create();
    $user->assignRole('cliente');

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/settings/prices');

    $response->assertStatus(403);
});