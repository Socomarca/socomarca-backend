<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\DestroyRequest;
use App\Http\Requests\Users\ShowRequest;
use App\Http\Requests\Users\StoreRequest;
use App\Http\Requests\Users\UpdateRequest;
use App\Http\Resources\Users\ProfileResource;
use App\Http\Resources\Users\UserCollection;
use App\Http\Resources\Users\UserResource;
use App\Mail\UserNotificationMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $perPage = request()->input('per_page', 20);
        $users = User::with('roles')->paginate($perPage);

        $data = new UserCollection($users);

        return $data;
    }

    /**
     * Store a newly created user in storage.
     * Requires manage-users permission.
     *
     * @param StoreRequest $storeRequest
     * @return JsonResponse
     */
    public function store(StoreRequest $storeRequest): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $storeRequest->validated();

            // Generar contraseña si no se proporciona
            $password = $data['password'] ?? Str::random(12);
            $isPasswordGenerated = !isset($data['password']);

            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($password);
            $user->phone = $data['phone'];
            $user->rut = $data['rut'];
            $user->business_name = $data['business_name'];
            $user->is_active = $data['is_active'];
            $user->save();

            // Asignar roles si se proporcionan
            if (isset($data['roles']) && is_array($data['roles'])) {
                $user->assignRole($data['roles']);
            } else {
                // Asignar rol por defecto 'cliente' si no se especifica
                $user->assignRole('cliente');
            }

            // Enviar email de notificación
            try {
                Mail::to($user->email)->send(
                    new UserNotificationMail(
                        $user, 
                        'created', 
                        $isPasswordGenerated ? $password : null
                    )
                );
            } catch (\Exception $e) {
                Log::error('Error enviando email de creación de usuario: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Error: No se pudo enviar el email de creación de usuario',
                    'error' => config('app.debug') ? $e->getMessage() : 'No se pudo crear el usuario'
                ], 500);
            }

            DB::commit();

            return response()->json([
                'message' => 'Usuario creado exitosamente',
                'user' => new UserResource($user->load('roles')),
                'password_generated' => $isPasswordGenerated
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creando usuario: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'No se pudo crear el usuario'
            ], 500);
        }
    }

    public function show($id)
    {
        if (!User::find($id))
        {
            return response()->json(
            [
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        $user = User::with(['billing_address', 'shipping_addresses', 'roles'])
            ->find($id);

        return response()->json(new UserResource($user));
    }

    /**
     * Update the specified user in storage.
     * Requires manage-users permission.
     *
     * @param UpdateRequest $updateRequest
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateRequest $updateRequest, $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $updateRequest->validated();

            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado.',
                ], 404);
            }

            // Guardar datos originales para comparar cambios
            $originalData = $user->toArray();

            // Actualizar datos básicos
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->phone = $data['phone'];
            $user->rut = $data['rut'];
            $user->business_name = $data['business_name'];
            $user->is_active = $data['is_active'];

            // Actualizar contraseña si se proporciona
            $passwordChanged = false;
            $newPassword = null;
            if (isset($data['password']) && !empty($data['password'])) {
                $newPassword = $data['password'];
                $user->password = Hash::make($newPassword);
                $passwordChanged = true;
            }

            $user->save();

            // Actualizar roles si se proporcionan
            if (isset($data['roles']) && is_array($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            // Verificar si hubo cambios significativos
            $hasSignificantChanges = $this->hasSignificantChanges($originalData, $user->toArray());

            // Enviar email de notificación solo si hay cambios significativos
            if ($hasSignificantChanges || $passwordChanged) {
                try {
                    Mail::to($user->email)->send(
                        new UserNotificationMail(
                            $user, 
                            'updated', 
                            $passwordChanged ? $newPassword : null
                        )
                    );
                } catch (\Exception $e) {
                    Log::error('Error enviando email de actualización de usuario: ' . $e->getMessage());
                    // Continuar sin fallar si hay error en el email
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Usuario actualizado exitosamente',
                'user' => new UserResource($user->load('roles')),
                'password_changed' => $passwordChanged
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando usuario: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'No se pudo actualizar el usuario'
            ], 500);
        }
    }

    /**
     * Remove the specified user from storage.
     * Requires manage-users permission.
     *
     * @param DestroyRequest $destroyRequest
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(DestroyRequest $destroyRequest, $id): JsonResponse
    {
        try {
            $destroyRequest->validated();

            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado.',
                ], 404);
            }

            // Verificar que no se pueda eliminar a sí mismo
            $currentUser = request()->user();
            if ($currentUser && $user->id === $currentUser->id) {
                return response()->json([
                    'message' => 'No puedes eliminar tu propia cuenta.',
                ], 422);
            }
            $user->cartItems()->delete();
            $user->favoritesList()->delete();

            // Verificar si el usuario tiene pedidos o datos críticos
            if ($user->cartItems()->exists() || $user->favoritesList()->exists()) {
                return response()->json([
                    'message' => 'No se puede eliminar el usuario porque tiene datos asociados (carrito, listas de favoritos, etc.).',
                ], 422);
            }

            $user->delete();

            return response()->json([
                'message' => 'Usuario eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error eliminando usuario: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'No se pudo eliminar el usuario'
            ], 500);
        }
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        return $user->toResource(ProfileResource::class);
        // return $user->toResource();
    }

    /**
     * Search users by filters
     * Requires manage-users permission
     *
     * @param Request $request
     *
     * @return UserCollection
     */
    public function search(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $filters = $request->input('filters', []);
        
        $result = User::select("users.*")
            ->with('roles')
            ->filter($filters)
            ->paginate($perPage);

        $data = new UserCollection($result);

        return $data;
    }

    /**
     * Check if there are significant changes in user data
     *
     * @param array $originalData
     * @param array $newData
     * @return bool
     */
    private function hasSignificantChanges(array $originalData, array $newData): bool
    {
        $fieldsToCheck = ['name', 'email', 'phone', 'rut', 'business_name', 'is_active'];
        
        foreach ($fieldsToCheck as $field) {
            if (($originalData[$field] ?? '') !== ($newData[$field] ?? '')) {
                return true;
            }
        }
        
        return false;
    }
}
