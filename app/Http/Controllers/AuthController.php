<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use App\Http\Requests\AuthRequest;
use Illuminate\Http\Request;


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
        $user = $request->auth_user;

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
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'rut' => $user->rut,
                'email' => $user->email,
            ]
                // 'roles' => $roles,
                // 'permissions' => $permissions,
                
            
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
