<?php

use App\Models\User;
use Freshwork\ChileanBundle\Rut;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Helper para autenticarse fácilmente
function authenticatedUser(): User
{
    return User::factory()->create();
}

test('user with manage-users permission can create another user', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage-users');

    $randomNumber = rand(1000000, 25000000);
    $rut = new Rut($randomNumber);
    $formattedRut = $rut->fix()->format();
    $data = [
        'name' => fake()->firstName() . ' ' . fake()->lastName(),
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '912345678',
        'rut' => $formattedRut,
        'business_name' => fake()->company(),
        'is_active' => true,
    ];

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', $data)
        ->assertCreated();
});

test('failure when creating a user with invalid data', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage-users');

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'name',
            'email',
            'password',
            'phone',
            'rut',
            'business_name',
            'is_active',
        ]);
});

test('failure when user email is not valid', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage-users');

    $data = User::factory()->make([
        'email' => 'correo-no-valido',
    ])->toArray();
    $data['password'] = 'password123';
    $data['password_confirmation'] = 'password123';

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', $data)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('failure when creating a user with an already registered email', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage-users');

    $email = fake()->email;
    User::factory()->create(['email' => $email]);

    $data = [
        'name' => fake()->firstName() . ' ' . fake()->lastName(),
        'email' => $email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => fake()->phoneNumber,
        'rut' => '12345678-9',
        'business_name' => fake()->company,
        'is_active' => true,
    ];

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('failure when phone number is not 9 digits', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage-users');

    $data = User::factory()->make(['phone' => 12345])->toArray();
    $data['password'] = 'password123';
    $data['password_confirmation'] = 'password123';

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', $data)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['phone']);
});

test('failure when RUT is invalid', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage-users');

    $data = User::factory()->make(['rut' => '1234567-9'])->toArray();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', [
            ...$data,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['rut']);
});

test('user with manage-users permission can update another user password and roles', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');

    $user = User::factory()->create();
    $originalEmail = $user->email;

    $data = [
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
        'roles' => ['cliente'],
    ];

    $this->actingAs($admin, 'sanctum')
        ->patchJson("/api/users/{$user->id}", $data)
        ->assertStatus(200);

    $user->refresh();
    // Email no debe cambiar ya que no está en los datos enviados
    expect($user->email)->toBe($originalEmail);
    // Verificar que tiene el rol asignado
    expect($user->hasRole('cliente'))->toBeTrue();
});

test('success when only updating password without other fields', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');

    $user = User::factory()->create();

    $data = [
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ];

    $this->actingAs($admin, 'sanctum')
        ->patchJson("/api/users/{$user->id}", $data)
        ->assertStatus(200);
});

test('success when no fields are provided for updating', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');

    $user = User::factory()->create();

    $this->actingAs($admin, 'sanctum')
        ->patchJson("/api/users/{$user->id}", [])
        ->assertStatus(200);
});

test('failure when password confirmation does not match', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    $user = User::factory()->create();

    $data = [
        'password' => 'newpassword123',
        'password_confirmation' => 'differentpassword',
    ];

    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/users/{$user->id}", $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});
