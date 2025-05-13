<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'rut' => 'required|string|max:12|unique:users',
            'password' => 'required|string|min:8',
            //'phone' => 'nullable|string|max:20',
            //'business_name' => 'nullable|string|max:255',
            //'role' => 'required|string|in:cliente,vendedor,admin,super-admin'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'rut' => $request->rut,
            'password' => Hash::make($request->password),
            // 'phone' => $request->phone,
            // 'business_name' => $request->business_name,
            'is_active' => true,
            'last_login' => Carbon::now(),
        ]);

        // Asignar rol al usuario
       //$user->assignRole($request->role);

        // Crear token con el nombre del dispositivo
        $tokenName = $request->device_name ?? 'test-device';
        $token = $user->createToken($tokenName)->plainTextToken;

        // Obtener roles y permisos
        //$roles = $user->getRoleNames();
        //$permissions = $user->getAllPermissions()->pluck('name');

        return response()->json([
            'status' => true,
            'message' => 'Usuario registrado exitosamente',
            'data' => [
                'user' => $user,
                // 'roles' => $roles,
                // 'permissions' => $permissions,
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 201);
    }
    /**
     * Login con email y contraseña
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rut' => 'required',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

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
    public function logout(Request $request)
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
