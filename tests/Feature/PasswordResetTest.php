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

// Aquí irán los tests

test('el usuario puede solicitar restablecimiento de contraseña con RUT válido', function () {
    Mail::fake();
    Event::fake();

    $user = User::factory()->create([
        'rut' => '11111111-1',
        'email' => 'test@example.com',
    ]);

    $response = $this->postJson(route('password.email'), [
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
    
    // Verificar que el password se actualizó (no podemos saber el valor exacto)
    $updatedUser = User::where('rut', $user->rut)->first();
    $this->assertTrue(Hash::check($response->json('data.temporary_password'), $updatedUser->password));


    Mail::assertSent(TemporaryPasswordMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('solicitud de restablecimiento falla si el RUT no existe', function () {
    Mail::fake();

    $response = $this->postJson(route('password.email'), [
        'rut' => '00000000-0',
    ]);

    $response->assertStatus(422);
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

    $response = $this->postJson(route('password.change'), [
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

    $response = $this->postJson(route('password.change'), [
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

    $response = $this->postJson(route('password.change'), [
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

    // Contraseña nueva muy corta
    $response = $this->postJson(route('password.change'), [
        'current_password' => 'currentpassword',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrorFor('password');

    // Contraseñas no coinciden
    $response = $this->postJson(route('password.change'), [
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


// Tests para verifyTokenByRut y resetPasswordByRut
// Estos son más complejos porque interactúan con la tabla password_reset_tokens
// y el facade Password de Laravel.

test('se puede verificar un token de restablecimiento de contraseña por RUT valido', function () {
    $user = User::factory()->create(['email' => 'verifyme@example.com', 'rut' => '22222222-2']);
    $token = Str::random(60); // Token de ejemplo
    
    // Simular la creación de un token en la BD
    DB::table('password_reset_tokens')->insert([
        'email' => $user->email,
        'token' => Hash::make($token), // Laravel almacena el hash del token
        'created_at' => Carbon::now()
    ]);

    $response = $this->postJson(route('password.verify'), [
        'rut' => '22222222-2',
        'token' => $token,
    ]);

    dd($response->getContent());
    
    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'Token válido',
            'valid' => true
        ]);
});

test('verificacion de token falla con token invalido o expirado por RUT', function () {
    $user = User::factory()->create(['email' => 'verifyfail@example.com', 'rut' => '33333333-3']);
    
    // No se crea ningún token en la BD, o se podría crear uno expirado o diferente

    $response = $this->postJson(route('password.verify'), [
        'rut' => '33333333-3',
        'token' => 'invalidtoken',
    ]);
    
    $response->assertStatus(400) // O el código de error que devuelva tu lógica
        ->assertJson([
            'status' => false,
            'message' => 'Token inválido o expirado',
            'valid' => false
        ]);
});

test('verificacion de token falla si el RUT no existe para verifyTokenByRut', function () {
    $response = $this->postJson(route('password.verify'), [
        'rut' => '00000000-0', // RUT no existente
        'token' => 'sometoken',
    ]);
    
    $response->assertStatus(404)
        ->assertJson([
            'status' => false,
            'message' => 'No se encontró un usuario con ese RUT',
            'errors' => ['rut' => ['Usuario no encontrado']]
        ]);
});


test('usuario puede restablecer contraseña con token valido por RUT', function () {
    Event::fake(); // Evita que se dispare el evento PasswordReset real si no es necesario para el test
    
    $user = User::factory()->create([
        'email' => 'resetme@example.com', 
        'rut' => '44444444-4',
        'password_changed_at' => Carbon::now()->subDay() // Para asegurar que no sea null
    ]);
    $token = Password::broker()->createToken($user); // Generar un token válido

    $newPassword = 'newPasswordAfterReset';

    $response = $this->postJson(route('password.reset'), [
        'rut' => '44444444-4',
        'token' => $token,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'Contraseña restablecida correctamente'
        ]);

    $user->refresh();
    $this->assertTrue(Hash::check($newPassword, $user->password));
    $this->assertNotNull($user->password_changed_at); // Asumiendo que se actualiza al cambiarla
    Event::assertDispatched(PasswordReset::class, function ($event) use ($user) {
        return $event->user->is($user);
    });
});


test('restablecimiento de contraseña falla con token invalido por RUT', function () {
    $user = User::factory()->create(['rut' => '55555555-5']);
    $newPassword = 'newPasswordFailedReset';

    $response = $this->postJson(route('password.reset'), [
        'rut' => '55555555-5',
        'token' => 'invalidtoken',
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);
    
    $response->assertStatus(400)
        ->assertJson([
            'status' => false,
            // El mensaje exacto dependerá de la implementación de Password::reset y las traducciones
            'message' => 'No se pudo restablecer la contraseña', 
        ]);
});

test('restablecimiento de contraseña falla si el RUT no existe', function () {
    $newPassword = 'newPasswordNoUser';

    $response = $this->postJson(route('password.reset'), [
        'rut' => '00000000-0', // RUT no existente
        'token' => 'sometoken',
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'status' => false,
            'message' => 'No se encontró un usuario con ese RUT',
            'errors' => ['rut' => ['Usuario no encontrado']]
        ]);
});

test('restablecimiento de contraseña falla por validacion de password por RUT', function () {
    $user = User::factory()->create(['email' => 'resetvalidate@example.com', 'rut' => '66666666-6']);
    $token = Password::broker()->createToken($user);

    // Contraseña muy corta
    $response = $this->postJson(route('password.reset'), [
        'rut' => '66666666-6',
        'token' => $token,
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);
    $response->assertStatus(422)->assertJsonValidationErrorFor('password');

    // Contraseñas no coinciden
    $response = $this->postJson(route('password.reset'), [
        'rut' => '66666666-6',
        'token' => $token,
        'password' => 'newpassword123',
        'password_confirmation' => 'differentpassword123',
    ]);
    $response->assertStatus(422)->assertJsonValidationErrorFor('password');
}); 