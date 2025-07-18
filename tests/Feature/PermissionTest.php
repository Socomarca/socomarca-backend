<?php

test('admin user can list permissions', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $user = \App\Models\User::factory()->create();
    $user->assignRole('admin');
    $url = route('permissions.index');
    $this->actingAs($user, 'sanctum')
        ->getJson($url)
        ->assertStatus(200)
        ->assertJsonStructure([
            [
                'id',
                'name',
                'permissions' => [
                    [
                        'id',
                        'name',
                    ]
                ],
            ]
        ]);
});

test('supervisor cannot list permissions', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $user = \App\Models\User::factory()->create();
    $user->assignRole('supervisor');
    $url = route('permissions.index');
    $this->actingAs($user, 'sanctum')
        ->getJson($url)
        ->assertForbidden();
});
