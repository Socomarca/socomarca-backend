<?php

namespace Tests\Feature;

use App\Models\Siteinfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

// Prepara los roles necesarios antes de cada test
beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
});

test('show endpoint returns default structure when database is empty', function () {
    $this->getJson('/api/siteinfo')
        ->assertOk()
        ->assertJsonStructure([
            'header' => ['contact_phone', 'contact_email'],
            'footer' => ['contact_phone', 'contact_email'],
            'social_media' => [['label', 'link']]
        ]);
});

test('show endpoint returns saved values from database', function () {
    Siteinfo::create(['key' => 'header', 'value' => ['contact_phone' => '123', 'contact_email' => 'a@b.com']]);
    Siteinfo::create(['key' => 'footer', 'value' => ['contact_phone' => '456', 'contact_email' => 'c@d.com']]);
    Siteinfo::create(['key' => 'social_media', 'value' => [['label' => 'fb', 'link' => 'fb.com']]]);

    $this->getJson('/api/siteinfo')
        ->assertOk()
        ->assertJson([
            'header' => ['contact_phone' => '123', 'contact_email' => 'a@b.com'],
            'footer' => ['contact_phone' => '456', 'contact_email' => 'c@d.com'],
            'social_media' => [['label' => 'fb', 'link' => 'fb.com']]
        ]);
});

test('update endpoint requires authentication and correct role', function () {
    $this->putJson('/api/siteinfo', [])->assertUnauthorized();

    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');
    $this->putJson('/api/siteinfo', [])->assertForbidden();
});

test('an admin can update siteinfo', function () {
    $user = User::factory()->create()->assignRole('admin');

    $payload = [
        'header' => ['contact_phone' => '999', 'contact_email' => 'x@y.com'],
        'footer' => ['contact_phone' => '888', 'contact_email' => 'z@w.com'],
        'social_media' => [['label' => 'ig', 'link' => 'ig.com']]
    ];

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/siteinfo', $payload)
        ->assertOk()
        ->assertJson(['message' => 'Siteinfo updated successfully']);

    $this->assertDatabaseHas('siteinfo', ['key' => 'header']);
    $this->assertDatabaseHas('siteinfo', ['key' => 'footer']);
    $this->assertDatabaseHas('siteinfo', ['key' => 'social_media']);
});

test('terms and privacy policy endpoints return correct content', function () {
    Siteinfo::create(['key' => 'terms', 'content' => '<h1>Terms</h1>']);
    Siteinfo::create(['key' => 'privacy_policy', 'content' => '<h1>Policy</h1>']);

    $this->getJson('/api/terms')
        ->assertOk()
        ->assertJson(['content' => '<h1>Terms</h1>']);

    $this->getJson('/api/privacy-policy')
        ->assertOk()
        ->assertJson(['content' => '<h1>Policy</h1>']);
});

test('an editor can update terms and privacy policy', function () {
    $user = User::factory()->create()->assignRole('editor');

    $this->actingAs($user, 'sanctum');

    $this->putJson('/api/terms', ['content' => '<h1>New Terms</h1>'])
        ->assertOk()
        ->assertJson(['message' => 'Terms upadated succesfully']);

    $this->putJson('/api/privacy-policy', ['content' => '<h1>New Policy</h1>'])
        ->assertOk()
        ->assertJson(['message' => 'Privacy Policy updated successfully']);

    $this->assertDatabaseHas('siteinfo', ['key' => 'terms', 'content' => '<h1>New Terms</h1>']);
    $this->assertDatabaseHas('siteinfo', ['key' => 'privacy_policy', 'content' => '<h1>New Policy</h1>']);
});

test('customer message endpoint returns default structure', function () {
    $this->getJson('/api/customer-message')
        ->assertOk()
        ->assertJsonStructure([
            'header' => ['color', 'content'],
            'banner' => ['desktop_image', 'mobile_image', 'enabled'],
            'modal' => ['image', 'enabled']
        ]);
});

test('a superadmin can update customer message with images', function () {
    Storage::fake('public');
    $user = User::factory()->create()->assignRole('superadmin');
     $imagePath = public_path('images/test-image.png'); 

    
    if (!file_exists($imagePath)) {
        if (!is_dir(dirname($imagePath))) {
            mkdir(dirname($imagePath), 0755, true);
        }
        // Crea una imagen PNG válida de 1x1 pixel
        file_put_contents($imagePath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));
    }
    $payload = [
        'header_color' => '#fff',
        'header_content' => '<h1>Hola</h1>',
        'banner_enabled' => true,
        'modal_enabled' => true,
        'banner_desktop_image' => new UploadedFile($imagePath, 'desktop.png', 'image/png', null, true),
        'banner_mobile_image' => new UploadedFile($imagePath, 'mobile.png', 'image/png', null, true),
        'modal_image' => new UploadedFile($imagePath, 'modal.png', 'image/png', null, true),
    ];

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/customer-message', $payload)
        ->assertOk()
        ->assertJson(['message' => 'Mensaje de bienvenida actualizado correctamente.']);

    $this->assertDatabaseHas('siteinfo', ['key' => 'customer_message']);
});

test('a superadmin can update customer message without images', function () {
    $user = User::factory()->create()->assignRole('superadmin');

    $payload = [
        'header_color' => '#fff',
        'header_content' => '<h1>Hola</h1>',
        'banner_enabled' => true,
        'modal_enabled' => true,
    ];

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/customer-message', $payload)
        ->assertOk()
        ->assertJson(['message' => 'Mensaje de bienvenida actualizado correctamente.']);

    $this->assertDatabaseHas('siteinfo', ['key' => 'customer_message']);
});

// Webpay tests

test('webpay config endpoint returns default structure when database is empty', function () {
    $this->getJson('/api/webpay/config')
        ->assertStatus(404)
        ->assertJson([
            'message' => 'No se encontró la configuración de Webpay',
            'data' => [],
        ]);
});

test('webpay config endpoint returns stored values', function () {
    $stored = [
        'WEBPAY_COMMERCE_CODE' => '123456',
        'WEBPAY_API_KEY' => 'SOMEKEY',
        'WEBPAY_ENVIRONMENT' => 'production',
        'WEBPAY_RETURN_URL' => 'https://example.com/return',
    ];
    Siteinfo::create([
        'key' => 'WEBPAY_INFO',
        'value' => $stored,
        'content' => 'Informacion de entorno webpay',
    ]);

    $this->getJson('/api/webpay/config')
        ->assertOk()
        ->assertJson($stored);
});

test('update webpay config requires authentication and superadmin role', function () {
    $payload = [
        'WEBPAY_COMMERCE_CODE' => '111',
        'WEBPAY_API_KEY' => 'KEY',
        'WEBPAY_ENVIRONMENT' => 'integration',
        'WEBPAY_RETURN_URL' => 'https://abc.com',
    ];

    // Unauthenticated
    $this->putJson('/api/webpay/config', $payload)->assertUnauthorized();

    // Authenticated but not superadmin
    $user = User::factory()->create()->assignRole('admin');
    $this->actingAs($user, 'sanctum');
    $this->putJson('/api/webpay/config', $payload)->assertForbidden();
});

test('a superadmin can update webpay config', function () {
    $user = User::factory()->create()->assignRole('superadmin');

    $payload = [
        'WEBPAY_COMMERCE_CODE' => '7654321',
        'WEBPAY_API_KEY' => 'NEWKEY',
        'WEBPAY_ENVIRONMENT' => 'integration',
        'WEBPAY_RETURN_URL' => 'https://mysite.com/webpay/return',
    ];

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/webpay/config', $payload)
        ->assertOk()
        ->assertJson(['message' => 'Configuración de Webpay actualizada exitosamente']);

    $this->assertDatabaseHas('siteinfo', [
        'key' => 'WEBPAY_INFO',
    ]);

    $record = Siteinfo::where('key', 'WEBPAY_INFO')->first();
    expect($record->value)->toMatchArray($payload);
});