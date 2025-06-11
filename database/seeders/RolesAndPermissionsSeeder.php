<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lista de permisos
        $permissions = [
            "can-see-own-purchases",
            "can-see-all-reports",
            "can-see-all-products",
            "can-see-all-clients",
            "can-see-all-purchases",
            "can-edit-content",
            "can-edit-products",
            "can-manage-users",
            "can-manage-categories",
            "can-manage-admins",
        ];

        // Crear permisos si no existen
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles y sus permisos
        $roles = [
            'superadmin' => [
                "see-own-purchases",
                "see-all-reports",
                "see-all-products",
                "see-all-clients",
                "see-all-purchases",
                "edit-content",
                "edit-products",
                "manage-users",
                "manage-categories",
                "manage-admins"
            ],
            'admin' => [
                "see-own-purchases",
                "see-all-reports",
                "see-all-products",
                "see-all-clients",
                "see-all-purchases",
                "edit-content",
                "edit-products",
                "manage-users",
                "manage-categories"
            ],
            'supervisor' => [
                "see-own-purchases",
                "see-all-reports",
                "see-all-products",
                "see-all-clients",
                "see-all-purchases"
            ],
            'editor' => [
                "see-own-purchases",
                "see-all-products",
                "edit-content"
            ],
            'cliente' => [
                "see-own-purchases"
            ],
        ];

        // Crear roles y asignar permisos
        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($perms);
        }

        // Asignar roles a usuarios de ejemplo (ajusta los IDs según tus usuarios)
        $superadmin = User::find(1);
        if ($superadmin) {
            $superadmin->assignRole('superadmin');
        }

        $admin = User::find(2);
        if ($admin) {
            $admin->assignRole('admin');
        }

        $supervisor = User::find(3);
        if ($supervisor) {
            $supervisor->assignRole('supervisor');
        }

        $editor = User::find(4);
        if ($editor) {
            $editor->assignRole('editor');
        }

        $cliente = User::find(5);
        if ($cliente) {
            $cliente->assignRole('cliente');
        }
    }
}
