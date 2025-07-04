<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchUsersRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Obtener todos los roles del sistema
     * Solo accesible para usuarios con roles admin y superadmin
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();

        $result = $roles->map(function($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at,
            ];
        });

        return response()->json($result);
    }

    public function rolesWithUsers()
    {
        $roles = Role::all();

        $result = $roles->map(function($role) {
        // Obtén los usuarios que tienen este rol
        $users = User::role($role->name)->get(['id', 'name', 'email']);

        return [
                'role' => $role->name,
                'users' => $users,
            ];
        });

        return response()->json($result);
    }


    public function userRoles(User $user)
    {
        
        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');
        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }


}