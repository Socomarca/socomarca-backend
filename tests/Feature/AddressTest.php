<?php

use App\Models\Address;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function ()
{
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->addressListJsonStructure = [
            'data' => array
            (
                [
                    'id',
                    'address_line1',
                    'address_line2',
                    'postal_code',
                    'is_default',
                    'type',
                    'phone',
                    'contact_name',
                    'user' =>
                    [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'rut',
                        'business_name',
                        'created_at',
                        'updated_at',
                    ],
                    'municipality' =>
                    [
                        'id',
                        'name',
                        'code',
                        'region_id',
                        'created_at',
                        'updated_at',
                    ],
                    'created_at',
                    'updated_at',
                ],
            ),
        ];
});

test('verify authentication for addresses list', function ()
{
    $response = $this->getJson('/api/addresses/');
    $response->assertUnauthorized();
});

test('verify user address list', function ()
{
    $addressCount = random_int(1, 5);
    Address::truncate();
    $user = User::factory()
        ->has(
            Address::factory()
                ->count($addressCount)
        )
            ->create();

    $user->assignRole('cliente');

    $route = route('addresses.index');
    $this->actingAs($user, 'sanctum')
        ->getJson($route)
        ->assertStatus(200)
        ->assertJsonStructure($this->addressListJsonStructure)
        ->assertJsonCount($addressCount, 'data');
});

test('verify customer cannot see other users addresses', function ()
{
    $addressCount = random_int(1, 5);
    Address::truncate();
    $user = User::factory()
        ->has(
            Address::factory()
                ->count($addressCount)
        )
            ->create();
    $address = $user->addresses()->first();

    $user->assignRole('cliente');

    $user2 = User::factory()->create();

    $route = route('addresses.show', ['address' => $address->id]);
    $this->actingAs($user, 'sanctum')
        ->getJson($route)
        ->assertStatus(200);

    $this->actingAs($user2, 'sanctum')
        ->getJson($route)
        ->assertStatus(403);
});

test('verify address not found', function ()
{
    Address::truncate();
    $user = User::factory()->create();
    $user->assignRole('cliente');

    $route = route('addresses.show', ['address' => 2]);
    $this->actingAs($user, 'sanctum')
        ->getJson($route)
        ->assertNotFound();
});
