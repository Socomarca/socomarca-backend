<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\AuthRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * Obtener token de acceso con RUT y contraseña
     * @param AuthRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(AuthRequest $request)
    {
        $user = User::where('rut', $request->rut)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            abort(401, "Unauthorized");
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
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Token deleted'
        ]);
    }
}
