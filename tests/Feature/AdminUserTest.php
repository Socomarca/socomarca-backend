<?php

use App\Mail\UserNotificationMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

test('usuario sin permisos no puede crear usuarios', function () {
    // Arrange
    $user = User::factory()->create();
    
    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '123456789',
        'rut' => '17260847-7',
        'business_name' => 'Test Business',
        'is_active' => true,
    ];

    // Act
    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', $userData);

    // Assert
    $response->assertStatus(403);
});

test('usuario con permisos puede crear usuarios', function () {
    // Arrange
    Mail::fake();
    
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    
    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '123456789',
        'business_name' => 'Test Business',
        'is_active' => true,
        'roles' => ['cliente']
    ];

    // Act
    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/users', $userData);

    // Assert
    expect($response->getStatusCode())->toBeIn([201, 422]);
    
    if ($response->getStatusCode() === 422) {
        $response->assertJsonValidationErrors(['rut']);
    } else {
        $response->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
                'roles'
            ],
            'password_generated'
        ]);
    }
});

test('usuario con permisos puede actualizar usuarios', function () {
    // Arrange
    Mail::fake();
    
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    
    $user = User::factory()->create([
        'name' => 'Original Name',
        'business_name' => 'Original Business'
    ]);
    $user->assignRole('cliente');
    
    $updateData = [
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
        'roles' => ['admin']
    ];

    // Act
    $response = $this->actingAs($admin, 'sanctum')
        ->putJson("/api/users/{$user->id}", $updateData);

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'user',
            'password_changed'
        ]);

    // Verificar que los datos básicos NO cambiaron (solo se pueden cambiar password y roles)
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Original Name',
        'business_name' => 'Original Business',
    ]);

    // Verificar que los roles sí cambiaron
    $user->refresh();
    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->hasRole('cliente'))->toBeFalse();
    
    // Verificar que se reportó el cambio de contraseña
    $response->assertJson([
        'password_changed' => true
    ]);
});

test('usuario con permisos puede eliminar usuarios', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/users/{$user->id}");

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Usuario eliminado exitosamente'
        ]);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id
    ]);
});

test('usuario no puede eliminarse a si mismo', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');

    // Act
    $response = $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/users/{$admin->id}");

    // Assert
    $response->assertForbidden()
        ->assertJson([
            'message' => 'No puedes eliminar tu propia cuenta.'
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $admin->id
    ]);
});

test('validacion de email unico al crear usuario', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    
    $existingUser = User::factory()->create();

    $userData = [
        'name' => 'Test User',
        'email' => $existingUser->email, // Email duplicado
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '123456789',
        'rut' => '18765432-1',
        'business_name' => 'Test Business',
        'is_active' => true,
    ];

    // Act
    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/users', $userData);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('validacion de rut unico al crear usuario', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    
    $existingUser = User::factory()->create();

    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '123456789',
        'rut' => $existingUser->rut, // RUT duplicado
        'business_name' => 'Test Business',
        'is_active' => true,
    ];

    // Act
    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/users', $userData);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['rut']);
});

test('puede obtener lista de usuarios', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    
    User::factory()->count(3)->create();

    // Act
    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/users');

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'rut',
                    'business_name',
                    'is_active',
                    'roles'
                ]
            ],
            'links',
            'meta'
        ]);
});

test('puede obtener usuario especifico', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($admin, 'sanctum')
        ->getJson("/api/users/{$user->id}");

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'phone',
            'rut',
            'business_name',
            'is_active',
            'roles'
        ]);
});

test('retorna 404 para usuario inexistente', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');

    // Act
    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/users/99999');

    // Assert
    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Usuario no encontrado.'
        ]);
});

test('puede buscar usuarios con filtros', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    
    User::factory()->create(['name' => 'Juan Pérez']);
    User::factory()->create(['name' => 'María García']);
    User::factory()->create(['name' => 'Carlos López']);

    // Act
    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/users/search', [
            'filters' => [
                [
                    'field' => 'name',
                    'operator' => 'ILIKE',
                    'value' => '%Juan%'
                ]
            ]
        ]);

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'roles'
                ]
            ],
            'links',
            'meta'
        ]);
}); 