<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rut' => 'required|exists:users,rut',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Buscar al usuario por RUT para obtener su email
        $user = User::where('rut', $request->rut)->first();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'No se encontró usuario',
                'errors' => ['rut' => ['Usuario no encontrado']]
            ], 404);
        }

        // Enviar el enlace de restablecimiento
         $status = Password::sendResetLink(
            ['email' => $user->email]
        );

        // envio al log en desarrollo

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => true,
                'message' => 'Se ha enviado un correo electrónico con el enlace para restablecer tu contraseña',

            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'No se pudo enviar el enlace de restablecimiento',
            'errors' => ['email' => [trans($status)]]
        ], 400);
    }



 

    /**
     * Verificar token por RUT en lugar de email
     */
    public function verifyTokenByRut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'rut' => 'required|exists:users,rut',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Buscar al usuario por RUT para obtener su email
        $user = User::where('rut', $request->rut)->first();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'No se encontró un usuario con ese RUT',
                'errors' => ['rut' => ['Usuario no encontrado']]
            ], 404);
        }

        // Verificar si existe una entrada en la tabla password_reset_tokens
        $tokenData = \DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->first();

        if (!$tokenData) {
            return response()->json([
                'status' => false,
                'message' => 'Token inválido o expirado',
                'valid' => false
            ], 400);
        }

        // Verificar si el token es válido
        $valid = Hash::check($request->token, $tokenData->token);

        return response()->json([
            'status' => true,
            'message' => $valid ? 'Token válido' : 'Token inválido',
            'valid' => $valid
        ]);
    }

    /**
     * Restablecer la contraseña por RUT
     */
    public function resetPasswordByRut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'rut' => 'required|exists:users,rut',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Buscar al usuario por RUT para obtener su email
        $user = User::where('rut', $request->rut)->first();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'No se encontró un usuario con ese RUT',
                'errors' => ['rut' => ['Usuario no encontrado']]
            ], 404);
        }

        // Restablecer la contraseña usando el email asociado al RUT
        $status = Password::reset(
            [
                'email' => $user->email,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'token' => $request->token
            ],
            function (User $resetUser, string $password) {
                $resetUser->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($resetUser));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'status' => true,
                'message' => 'Contraseña restablecida correctamente'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'No se pudo restablecer la contraseña',
            'errors' => ['rut' => [trans($status)]]
        ], 400);
    }
}
