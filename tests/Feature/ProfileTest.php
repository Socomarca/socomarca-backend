<?php

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

uses(RefreshDatabase::class);

test('usuario puede ver la información de su propio perfil', function () {
    $user = User::factory()
        ->has(
            Address::factory([
                'type' => 'shipping',
                'is_default' => 1,
            ])->count(1)
        )->has(
            Address::factory([
                'type' => 'billing',
                'is_default' => 1,
            ])->count(1)
        )->has(
            Address::factory([
                'type' => 'shipping',
                'is_default' => 0,
            ])->count(2)
        )->create();

    $addressStructure = [
            'id',
            'address_line1',
            'address_line2',
            'postal_code',
            'is_default',
            'type',
            'phone',
            'contact_name',
            'municipality_name',
            'region_name',
            'alias',
        ];

    $structure = [
        "rut",
        "name",
        "business_name",
        "email",
        "phone",
        "is_active",
        "billing_address" => $addressStructure,
        "default_shipping_address" => $addressStructure,
    ];

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/profile')
        ->assertStatus(200)
        ->assertJsonStructure($structure)
        ->assertJsonFragment(['rut' => $user->rut])
        ->assertJson(fn (AssertableJson $json) =>
            $json->where('rut', $user->rut)
                ->where('name', $user->name)
                ->where('billing_address.id', $user->billing_address->id)
                ->where('default_shipping_address.id', $user->default_shipping_address->id)
                ->etc()
        );;
});

test('usuario puede ver su propio perfil aunque no tenga direcciones asociadas', function () {
    $user = User::factory()->create();
    Address::where('user_id', $user->id)->delete();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/profile')
        ->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) =>
            $json->where('rut', $user->rut)
                ->where('name', $user->name)
                ->where('billing_address', null)
                ->where('default_shipping_address', null)
                ->etc()
        );
});
