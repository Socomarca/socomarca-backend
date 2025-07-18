<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Role::with('permissions')->get()
            ->map(function ($role) {
                $role->permissions->transform(function ($permission) {
                    return $permission->makeHidden(['guard_name', 'created_at', 'updated_at', 'pivot']);
                });
                return $role->makeHidden(['guard_name', 'created_at', 'updated_at']);
            });
    }
}
