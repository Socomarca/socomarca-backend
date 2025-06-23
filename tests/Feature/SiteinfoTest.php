<?php

namespace Tests\Feature;

use App\Models\Siteinfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SiteinfoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        }

    public function test_show_returns_default_structure_when_empty()
    {
        $response = $this->getJson('/api/siteinfo');
        $response->assertOk()
            ->assertJsonStructure([
                'header' => ['contact_phone', 'contact_email'],
                'footer' => ['contact_phone', 'contact_email'],
                'social_media' => [['label', 'link']]
            ]);
    }

    public function test_show_returns_saved_values()
    {
        Siteinfo::create(['key' => 'header', 'value' => ['contact_phone' => '123', 'contact_email' => 'a@b.com']]);
        Siteinfo::create(['key' => 'footer', 'value' => ['contact_phone' => '456', 'contact_email' => 'c@d.com']]);
        Siteinfo::create(['key' => 'social_media', 'value' => [['label' => 'fb', 'link' => 'fb.com']]]);

        $response = $this->getJson('/api/siteinfo');
        $response->assertOk()
            ->assertJson([
                'header' => ['contact_phone' => '123', 'contact_email' => 'a@b.com'],
                'footer' => ['contact_phone' => '456', 'contact_email' => 'c@d.com'],
                'social_media' => [['label' => 'fb', 'link' => 'fb.com']]
            ]);
    }

    public function test_update_requires_auth_and_role()
    {
        $response = $this->putJson('/api/siteinfo', []);
        $response->assertStatus(401);

        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $response = $this->putJson('/api/siteinfo', []);
        $response->assertStatus(403);
    }

    public function test_update_siteinfo()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user, 'sanctum');

        $payload = [
            'header' => ['contact_phone' => '999', 'contact_email' => 'x@y.com'],
            'footer' => ['contact_phone' => '888', 'contact_email' => 'z@w.com'],
            'social_media' => [['label' => 'ig', 'link' => 'ig.com']]
        ];

        $response = $this->putJson('/api/siteinfo', $payload);
        $response->assertOk()->assertJson(['message' => 'Siteinfo updated successfully']);

        $this->assertDatabaseHas('siteinfo', ['key' => 'header']);
        $this->assertDatabaseHas('siteinfo', ['key' => 'footer']);
        $this->assertDatabaseHas('siteinfo', ['key' => 'social_media']);
    }

    public function test_terms_and_privacy_policy_endpoints()
    {
        Siteinfo::create(['key' => 'terms', 'content' => '<h1>Terms</h1>']);
        Siteinfo::create(['key' => 'privacy_policy', 'content' => '<h1>Policy</h1>']);

        $this->getJson('/api/terms')
        ->assertOk()
        ->assertJson([
            'data' => ['content' => '<h1>Terms</h1>']
        ]);
        $this->getJson('/api/privacy-policy')
        ->assertOk()
        ->assertJson([
            'data' => ['content' => '<h1>Policy</h1>']
        ]);
    }

    public function test_update_terms_and_privacy_policy()
    {
        $user = User::factory()->create();
        $user->assignRole('editor');
        $this->actingAs($user, 'sanctum');

        $this->putJson('/api/terms', ['content' => '<h1>New Terms</h1>'])
            ->assertOk()->assertJson(['message' => 'Terms upadated succesfully']);
        $this->putJson('/api/privacy-policy', ['content' => '<h1>New Policy</h1>'])
            ->assertOk()->assertJson(['message' => 'Privacy Policy updated successfully']);

        $this->assertDatabaseHas('siteinfo', ['key' => 'terms', 'content' => '<h1>New Terms</h1>']);
        $this->assertDatabaseHas('siteinfo', ['key' => 'privacy_policy', 'content' => '<h1>New Policy</h1>']);
    }

    public function test_customer_message_get_and_update()
    {
        // GET vacío
        $this->getJson('/api/customer-message')
            ->assertOk()
            ->assertJsonStructure([
                'header' => ['color', 'content'],
                'banner' => ['desktop_image', 'mobile_image', 'enabled'],
                'modal' => ['image', 'enabled']
            ]);

        // UPDATE
        Storage::fake('public');
        $user = User::factory()->create();
        $user->assignRole('superadmin');
        $this->actingAs($user, 'sanctum');

        $payload = [
            'header_color' => '#fff',
            'header_content' => '<h1>Hola</h1>',
            'banner_enabled' => true,
            'modal_enabled' => true,
            'banner_desktop_image' => new \Illuminate\Http\UploadedFile(public_path('img1.png'), 'img1.png', null, null, true),
            'banner_mobile_image' => new \Illuminate\Http\UploadedFile(public_path('img1.png'), 'img1.png', null, null, true),
            'modal_image' => new \Illuminate\Http\UploadedFile(public_path('img1.png'), 'img1.png', null, null, true),
        ];

        
        $response = $this->postJson('/api/customer-message', $payload);
        $response->assertOk()->assertJson(['message' => 'Mensaje de bienvenida actualizado correctamente.']);

        $this->assertDatabaseHas('siteinfo', ['key' => 'customer_message']);
    }

    public function test_customer_message_get_and_update_without_images()
    {
        // GET vacío
        $this->getJson('/api/customer-message')
            ->assertOk()
            ->assertJsonStructure([
                'header' => ['color', 'content'],
                'banner' => ['desktop_image', 'mobile_image', 'enabled'],
                'modal' => ['image', 'enabled']
            ]);

        // UPDATE sin imágenes
        $user = User::factory()->create();
        $user->assignRole('superadmin');
        $this->actingAs($user, 'sanctum');

        $payload = [
            'header_color' => '#fff',
            'header_content' => '<h1>Hola</h1>',
            'banner_enabled' => true,
            'modal_enabled' => true,
            
        ];

        $response = $this->postJson('/api/customer-message', $payload);
        $response->assertOk()->assertJson(['message' => 'Mensaje de bienvenida actualizado correctamente.']);

        $this->assertDatabaseHas('siteinfo', ['key' => 'customer_message']);
    }

    public function test_customer_message_update_with_real_image()
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');
        $this->actingAs($user, 'sanctum');

        // Ruta a una imagen real en public/
        $imagePath = public_path('img1.png'); 

        $payload = [
            'header_color' => '#fff',
            'header_content' => '<h1>Hola</h1>',
            'banner_enabled' => true,
            'modal_enabled' => true,
            'banner_desktop_image' => new \Illuminate\Http\UploadedFile(public_path('img1.png'), 'img1.png', null, null, true),
            'banner_mobile_image'  => new \Illuminate\Http\UploadedFile(public_path('img1.png'), 'img1.png', null, null, true),
            'modal_image'          => new \Illuminate\Http\UploadedFile(public_path('img1.png'), 'img1.png', null, null, true),
        ];

        $response = $this->postJson('/api/customer-message', $payload);
        $response->assertOk()->assertJson(['message' => 'Mensaje de bienvenida actualizado correctamente.']);

        $this->assertDatabaseHas('siteinfo', ['key' => 'customer_message']);
    }
}