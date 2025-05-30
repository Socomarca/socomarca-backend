<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Mail;
use App\Mail\TemporaryPasswordMail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);



test('el usuario puede solicitar restablecimiento de contraseña con RUT válido', function () {
    Mail::fake();
    Event::fake();

    $user = User::factory()->create([
        'rut' => '11111111-1',
        'email' => 'test@example.com',
    ]);

    $response = $this->postJson(route('auth.password.restore'), [
        'rut' => '11111111-1',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'A new provisional password has been sent',
            'data' => [
                'email' => $user->email,
            ]
        ])
        ->assertJsonStructure([
            'message',
            'data' => [
                'email',
                'temporary_password',
            ]
        ]);

    $this->assertDatabaseHas('users', [
        'rut' => $user->rut,
        'password_changed_at' => null,
    ]);
    
    
    $updatedUser = User::where('rut', $user->rut)->first();
    $this->assertTrue(Hash::check($response->json('data.temporary_password'), $updatedUser->password));


    Mail::assertSent(TemporaryPasswordMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('solicitud de restablecimiento falla si el RUT no existe', function () {
    Mail::fake();

    $response = $this->postJson(route('auth.password.restore'), [
        'rut' => '00000000-0',
    ]);

    $response->assertStatus(401);
});

test('usuario autenticado puede cambiar su contraseña', function () {
    Event::fake();
    $user = User::factory()->create([
        'rut' => '11111111-1',
        'password' => Hash::make('currentpassword'),
        'password_changed_at' => null,
    ]);

    $this->actingAs($user);

    $newPassword = 'newStrongPassword123';

    $response = $this->putJson(route('password.update'), [
        'current_password' => 'currentpassword',
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);


    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'Contraseña actualizada correctamente'
        ]);

    $user->refresh();
    $this->assertTrue(Hash::check($newPassword, $user->password));
    $this->assertNotNull($user->password_changed_at);
});

test('cambio de contraseña falla con contraseña actual incorrecta', function () {
    $user = User::factory()->create([
        'rut' => '11111111-1',
        'password' => Hash::make('currentpassword'),
    ]);

    $this->actingAs($user);

    $response = $this->putJson(route('password.update'), [
        'current_password' => 'wrongcurrentpassword',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(400);
});

test('cambio de contraseña falla si la nueva contraseña es igual a la actual', function () {
    $user = User::factory()->create([
        'rut' => '11111111-1',
        'password' => Hash::make('currentpassword'),
    ]);

    $this->actingAs($user);

    $response = $this->putJson(route('password.update'), [
        'current_password' => 'currentpassword',
        'password' => 'currentpassword',
        'password_confirmation' => 'currentpassword',
    ]);
    
    $response->assertStatus(422);
});


test('cambio de contraseña falla con validaciones incorrectas', function () {
    $user = User::factory()->create([
        'rut' => '11111111-1',
    ]);
    $this->actingAs($user);

    
    $response = $this->putJson(route('password.update'), [
        'current_password' => 'currentpassword',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrorFor('password');

    
    $response = $this->putJson(route('password.update'), [
        'current_password' => 'currentpassword',
        'password' => 'newpassword123',
        'password_confirmation' => 'anotherpassword123',
    ]);
    $response->assertStatus(422)->assertJsonValidationErrorFor('password');
});


test('usuario puede verificar el estado de su contraseña', function () {
    $user = User::factory()->create([
        'rut' => '11111111-1',
        'password_changed_at' => null,
    ]);
    $this->actingAs($user);

    $response = $this->getJson(route('password.status'));
    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'data' => [
                'needs_password_change' => true,
            ]
        ]);

    $user->password_changed_at = Carbon::now();
    $user->save();

    $response = $this->getJson(route('password.status'));
    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'data' => [
                'needs_password_change' => false,
            ]
        ]);
});
