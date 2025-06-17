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

    public function searchUsers(SearchUsersRequest $request)
    {
        // Obtén los roles a filtrar (o todos si no se especifica)
        $roles = $request->input('roles', ['admin', 'superadmin', 'supervisor', 'editor', 'cliente']);
        if (is_string($roles)) {
            $roles = explode(',', $roles);
        }

        $result = [];

        foreach ($roles as $roleName) {
            $users = User::role($roleName)
                ->when($request->filled('name'), function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->input('name') . '%');
                })
                ->when($request->filled('email'), function ($q) use ($request) {
                    $q->where('email', 'like', '%' . $request->input('email') . '%');
                })
                ->orderBy(
                    $request->input('sort_field', 'name'),
                    $request->input('sort_direction', 'asc')
                )
                ->get(['id', 'name', 'email', 'created_at']);

            $result[] = [
                'role' => $roleName,
                'users' => $users,
            ];
        }

        return response()->json($result);
    }
}