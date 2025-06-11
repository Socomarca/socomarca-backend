<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;

class ReportPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Crea el permiso si no existe
        Permission::firstOrCreate(['name' => 'see-all-reports']);
    }

    public function test_user_with_permission_can_access_reports()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('see-all-reports');

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/orders/reports', [
            'type' => 'sales'
        ]);

        $response->assertStatus(200); // O el status esperado
    }

    public function test_user_without_permission_cannot_access_reports()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/orders/reports', [
            'type' => 'sales'
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You do not have permission.'
            ]);
    }
}