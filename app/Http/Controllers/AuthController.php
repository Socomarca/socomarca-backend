<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\AuthRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    
    /**
     * Login con email y contraseña
     */
    public function login(AuthRequest $request)
    {
        

        $user = User::where('rut', $request->rut)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Las credenciales proporcionadas son incorrectas.',
                'errors' => [
                    'rut' => ['Las credenciales proporcionadas son incorrectas.']
                ]
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'status' => false,
                'message' => 'Tu cuenta está desactivada. Por favor contacta con soporte.'
            ], 403);
        }

        // Actualizar última vez de inicio de sesión
        $user->update([
            'last_login' => Carbon::now()
        ]);

        // Crear token con el nombre del dispositivo
        $tokenName = $request->device_name ?? 'unknown-device';
        $token = $user->createToken($tokenName)->plainTextToken;

        // Obtener roles y permisos
        //$roles = $user->getRoleNames();
        //$permissions = $user->getAllPermissions()->pluck('name');

        return response()->json([
            'status' => true,
            'message' => 'Inicio de sesión exitoso',
            'data' => [
                // 'user' => $user,
                // 'roles' => $roles,
                // 'permissions' => $permissions,
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Cerrar sesión (revocar token)
     */
    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    /**
     * Obtener información del usuario autenticado
     */
    public function me(Request $request)
    {
        
        $user = $request->user();
        // $roles = $user->getRoleNames();
        // $permissions = $user->getAllPermissions()->pluck('name');

        return response()->json([
            'status' => true,
            'data' => [
                'user' => $user,
                // 'roles' => $roles,
                // 'permissions' => $permissions
            ]
        ]);
    }
}
