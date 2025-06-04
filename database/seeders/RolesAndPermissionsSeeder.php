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
            'administrador' => [
                "can-see-own-purchases",
                "can-see-all-reports",
                "can-see-all-products",
                "can-see-all-clients",
                "can-see-all-purchases",
                "can-edit-content"
            ],
            'superAdministrador' => [
                "can-see-own-purchases",
                "can-see-all-products",
                "can-edit-products",
                "can-manage-users",
                "can-manage-categories",
                "can-see-all-purchases",
                "can-edit-content",
                "can-manage-admins"
            ],
            'colaborador' => [
                "can-see-own-purchases",
                "can-see-all-products",
                "can-edit-content"
            ],
            'editor' => [
                "can-see-own-purchases",
                "can-edit-content"
            ],
        ];

        // Crear roles y asignar permisos
        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($perms);
        }

        $admin = User::find(1);
        if ($admin) {
            $admin->assignRole('administrador');
        }

        $superAdmin = User::find(2);
        if ($superAdmin) {
            $superAdmin->assignRole('superAdministrador');
        }

        $colaborador = User::find(3);
        if ($colaborador) {
            $colaborador->assignRole('colaborador');
        }

        $editor = User::find(4);
        if ($editor) {
            $editor->assignRole('editor');
        }
    }
}
