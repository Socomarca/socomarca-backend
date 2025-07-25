<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Laragear\Rut\Facades\Generator;

beforeEach(function () {
    $this->user = createUser();

    // Crear permiso directamente
    $manageUsersPermission = Permission::firstOrCreate(['name' => 'manage-users']);

    // Crear roles y asignar permisos
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $adminRole->givePermissionTo($manageUsersPermission);

    $clientRole = Role::firstOrCreate(['name' => 'cliente']);

    // Usuario admin con permisos
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('admin');

    // Usuario cliente sin permisos
    $this->clientUser = User::factory()->create();
    $this->clientUser->assignRole('cliente');

    $this->userListJsonStructure = [
        'data' => [
            [
                'id',
                'name',
                'email',
                'phone',
                'rut',
                'business_name',
                'is_active',
                'last_login',
                'roles',
                'created_at',
                'updated_at',
            ],
        ],
        'meta' => [
            'current_page',
            'from',
            'last_page',
            'path',
            'per_page',
            'to',
            'total',
            'links' => [
                ['url', 'label', 'active']
            ],
        ]
    ];
});

test('requiere autenticacion con token', function () {
    $response = $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/users');

    $response->assertStatus(401);
});

test('retorna usuarios con estructura correcta', function () {
    $response = $this->actingAs($this->adminUser, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->get('/api/users');

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'rut',
                    'business_name',
                    'is_active',
                    'last_login',
                    'roles',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});

test('requiere permiso manage-users para busqueda', function () {
    $response = $this->actingAs($this->clientUser, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->postJson('/api/users/search');

    $response->assertStatus(403);
});

test('permite busqueda con permiso manage-users', function () {
    User::truncate();
    User::factory()->count(3)->create();

    $response = $this->actingAs($this->adminUser, 'sanctum')
        ->withHeaders(['Accept' => 'application/json'])
        ->postJson('/api/users/search');

    $response
        ->assertStatus(200)
        ->assertJsonStructure($this->userListJsonStructure);
});

test('filtra usuarios por nombre exacto', function () {
    User::truncate();

    User::factory()->create(['name' => 'Juan Pérez']);
    User::factory()->create(['name' => 'María González']);
    User::factory()->create(['name' => 'Carlos López']);

    $response = $this->actingAs($this->adminUser, 'sanctum')
        ->postJson('/api/users/search', [
            'filters' => [
                [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'Juan Pérez',
                ]
            ]
        ]);

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data')[0]['name'])->toBe('Juan Pérez');
    $response->assertStatus(200);
});

test('filtra usuarios por nombre parcial', function () {
    User::truncate();

    User::factory()->create(['name' => 'Juan Pérez']);
    User::factory()->create(['name' => 'Juana Martínez']);
    User::factory()->create(['name' => 'María González']);

    $response = $this->actingAs($this->adminUser, 'sanctum')
        ->postJson('/api/users/search', [
            'filters' => [
                [
                    'field' => 'name',
                    'operator' => 'ILIKE',
                    'value' => '%juan%',
                ]
            ]
        ]);

    expect($response->json('data'))->toHaveCount(2);
    foreach ($response->json('data') as $user) {
        expect(stripos($user['name'], 'juan'))->not->toBeFalse();
    }
    $response->assertStatus(200);
});

test('filtra usuarios por email', function () {
    User::truncate();

    User::factory()->create(['email' => 'juan@empresa.com']);
    User::factory()->create(['email' => 'maria@empresa.com']);
    User::factory()->create(['email' => 'carlos@otrodominio.com']);

    $response = $this->actingAs($this->adminUser, 'sanctum')
        ->postJson('/api/users/search', [
            'filters' => [
                [
                    'field' => 'email',
                    'operator' => 'ILIKE',
                    'value' => '%empresa%',
                ]
            ]
        ]);

    expect($response->json('data'))->toHaveCount(2);
    foreach ($response->json('data') as $user) {
        expect(stripos($user['email'], 'empresa'))->not->toBeFalse();
    }
    $response->assertStatus(200);
});

test('filtra usuarios por estado activo', function () {
    User::truncate();

    User::factory()->create(['is_active' => true]);
    User::factory()->create(['is_active' => false]);
    User::factory()->create(['is_active' => true]);

    $response = $this->actingAs($this->adminUser, 'sanctum')
        ->postJson('/api/users/search', [
            'filters' => [
                [
                    'field' => 'is_active',
                    'operator' => '=',
                    'value' => true,
                ]
            ]
        ]);

    expect($response->json('data'))->toHaveCount(2);
    foreach ($response->json('data') as $user) {
        expect($user['is_active'])->toBeTrue();
    }
    $response->assertStatus(200);
});

test('ordena usuarios por nombre', function () {
    User::truncate();

    User::factory()->create(['name' => 'Zebra García']);
    User::factory()->create(['name' => 'Ana López']);
    User::factory()->create(['name' => 'Beta Martínez']);

    $response = $this->actingAs($this->adminUser, 'sanctum')
        ->postJson('/api/users/search', [
            'filters' => [
                [
                    'field' => 'name',
                    'operator' => 'ILIKE',
                    'value' => '%',
                    'sort' => 'ASC'
                ]
            ]
        ]);

    $data = $response->json('data');
    expect($data)->toHaveCount(3);
    expect($data[0]['name'])->toBe('Ana López');
    expect($data[1]['name'])->toBe('Beta Martínez');
    expect($data[2]['name'])->toBe('Zebra García');
    $response->assertStatus(200);
});

test('filtra usuarios por rut', function () {
    User::truncate();

    User::factory()->create(['rut' => '12345678-9']);
    User::factory()->create(['rut' => '98765432-1']);
    User::factory()->create(['rut' => '12000000-0']);

    $response = $this->actingAs($this->adminUser, 'sanctum')
        ->postJson('/api/users/search', [
            'filters' => [
                [
                    'field' => 'rut',
                    'operator' => 'ILIKE',
                    'value' => '12%',
                ]
            ]
        ]);

    expect($response->json('data'))->toHaveCount(2);
    foreach ($response->json('data') as $user) {
        expect(str_starts_with($user['rut'], '12'))->toBeTrue();
    }
    $response->assertStatus(200);
});

test('combina múltiples filtros', function () {
    User::truncate();

    User::factory()->create(['name' => 'Juan Pérez', 'is_active' => true]);
    User::factory()->create(['name' => 'Juan García', 'is_active' => false]);
    User::factory()->create(['name' => 'María López', 'is_active' => true]);

    $response = $this->actingAs($this->adminUser, 'sanctum')
        ->postJson('/api/users/search', [
            'filters' => [
                [
                    'field' => 'name',
                    'operator' => 'ILIKE',
                    'value' => '%juan%'
                ],
                [
                    'field' => 'is_active',
                    'operator' => '=',
                    'value' => true
                ]
            ]
        ]);

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data')[0]['name'])->toBe('Juan Pérez');
    expect($response->json('data')[0]['is_active'])->toBeTrue();
    $response->assertStatus(200);
});

test('filtra usuarios por roles', function () {
    \App\Models\User::truncate();

    $admin = User::factory()->create(['name' => 'Ana Admin']);
    $admin->assignRole('admin');

    $cliente = User::factory()->create(['name' => 'Carlos Cliente']);
    $cliente->assignRole('cliente');


    $response = $this->actingAs($this->adminUser, 'sanctum')
        ->postJson('/api/users/search', [
            'roles' => ['admin', 'cliente'],
            'sort_field' => 'name',
            'sort_direction' => 'asc'
        ]);

    $data = $response->json('data');
    expect($data)->toHaveCount(2);
    expect(collect($data)->pluck('name'))->toContain('Ana Admin');
    expect(collect($data)->pluck('name'))->toContain('Carlos Cliente');

    $response->assertStatus(200);
});

test('ordena usuarios por nombre ascendente y por id descendente', function () {
    User::truncate();

    $juan = User::factory()->create(['name' => 'Juan Pérez']);
    $ana = User::factory()->create(['name' => 'Ana López']);
    $carlos = User::factory()->create(['name' => 'Carlos Gómez']);

    // Ordenar por nombre ascendente
    $responseAsc = $this->actingAs($this->adminUser, 'sanctum')
        ->postJson('/api/users/search', [
            'sort_field' => 'name',
            'sort_direction' => 'asc'
        ]);
    $namesAsc = array_column($responseAsc->json('data'), 'name');
    expect($namesAsc)->toBe(['Ana López', 'Carlos Gómez', 'Juan Pérez']);

    // Ordenar por id descendente
    $responseDesc = $this->actingAs($this->adminUser, 'sanctum')
        ->postJson('/api/users/search', [
            'sort_field' => 'id',
            'sort_direction' => 'desc'
        ]);
    $idsDesc = array_column($responseDesc->json('data'), 'id');
    $expectedDesc = collect([$juan, $ana, $carlos])->sortByDesc('id')->pluck('id')->values()->all();
    expect($idsDesc)->toBe($expectedDesc);
});

test('admin can register', function () {
    \Illuminate\Support\Facades\Mail::fake();
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $password = fake()->password(10, 12);
    $this->actingAs($admin, 'sanctum')
        ->postJson(route('users.store'), [
            "name" => fake()->firstName,
            "email" => fake()->email,
            "password" => $password,
            "password_confirmation" => $password,
            "phone" => "" . fake()->numberBetween(10000000, 99999999),
            "rut" => Generator::makeOne()->formatBasic(),
            "business_name" => fake()->company,
            "is_active" => true,
            "roles" => ['admin'],
        ]);
    \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\UserNotificationMail::class);
});

describe('admin can update user', function () {
    it('should perform a partial update', function() {
        \Illuminate\Support\Facades\Notification::fake();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('cliente');

        $payload = [
            'name' => fake()->name,
            "email" => fake()->email,
            "phone" => "" . fake()->numberBetween(10000000, 99999999),
            "is_active" => fake()->boolean(),
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/users/{$user->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'User updated successfully'])
            ->assertJsonFragment(['name' => $payload['name']])
            ->assertJsonFragment(['email' => $payload['email']])
            ->assertJsonFragment(['phone' => $payload['phone']]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $payload['name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'is_active' => $payload['is_active'],
        ]);

        \Illuminate\Support\Facades\Notification::assertSentTo($user, \App\Notifications\UserInfoUpdateNotification::class);
        \Illuminate\Support\Facades\Notification::assertNotSentTo($user, \App\Notifications\UserPasswordUpdateNotification::class);
    });

    it('should send the temporary password email after password update', function() {
        $this->freezeTime(function(\Illuminate\Support\Carbon $time) {
            \Illuminate\Support\Facades\Notification::fake();
            $admin = User::factory()->create();
            $admin->assignRole('admin');

            $user = User::factory()->create();
            $user->assignRole('cliente');
            $password = fake()->password(10, 12);
            $payload = [
                'password' => $password,
                'password_confirmation' => $password,
            ];

            $response = $this->actingAs($admin, 'sanctum')
                ->patchJson("/api/users/{$user->id}", $payload);

            $response
                ->assertJson(fn(\Illuminate\Testing\Fluent\AssertableJson $json) =>
                    $json->has('user.password_changed_at')
                        ->where('password_changed', true)
                        ->etc()
                );

            \Illuminate\Support\Facades\Notification::assertSentTo(
                $user,
                function(\App\Notifications\UserPasswordUpdateNotification $notification) use ($password) {
                    return $notification->temporaryPassword === $password;
                }
            );

            $user->refresh();
            expect($user->password_changed_at)->toBe($time->toDateTimeString());
        });
    });

    it('should fail when payload is incomplete during a full update', function() {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('cliente');

        $payload = [
            'name' => fake()->name,
        ];

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/users/{$user->id}", $payload)
            ->assertInvalid(['email', 'phone', 'is_active', 'password', 'roles']);
    });
});
