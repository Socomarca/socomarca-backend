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
            "read-own-purchases",
            "read-all-reports",
            "read-all-products",
            "read-all-customers",
            "read-all-purchases",
            "edit-content",
            "edit-products",
            "manage-users",
            "manage-categories",
            "manage-admins",

            // Address related permissions
            "read-all-addresses",
            "read-own-addresses",
            "create-address",
            "update-address",
            "delete-address",

            // FAQ related permissions
            "manage-faq",
            "create-faq",
            "update-faq",
            "delete-faq",

            // Favorite permissions
            "read-own-favorites",
            "create-favorites",
            "delete-favorites",

            // FavoritesList permissions
            "read-own-favorites-list",
            "create-favorites-list",
            "update-favorites-list",
            "delete-favorites-list",

            // List permissions names
            "read-all-permissions",
        ];

        // Crear permisos si no existen
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles y sus permisos
        $roles = [
            'superadmin' => [
                "read-own-purchases",
                "read-all-reports",
                "read-all-products",
                "read-all-customers",
                "read-all-purchases",
                "edit-content",
                "edit-products",
                "manage-users",
                "manage-categories",
                "manage-admins",

                // Address related permissions
                "read-all-addresses",
                "read-own-addresses",
                "create-address",
                "update-address",
                "delete-address",

                // FAQ related permissions
                "manage-faq",
                "create-faq",
                "update-faq",
                "delete-faq",

                // List permissions names
                "read-all-permissions",
            ],
            'admin' => [
                "read-own-purchases",
                "read-all-reports",
                "read-all-products",
                "read-all-customers",
                "read-all-purchases",
                "edit-content",
                "edit-products",
                "manage-users",
                "manage-categories",

                // Address related permissions
                "read-all-addresses",
                "read-own-addresses",
                "create-address",
                "update-address",
                "delete-address",

                // FAQ related permissions
                "manage-faq",
                "create-faq",
                "update-faq",
                "delete-faq",

                // List permissions names
                "read-all-permissions",
            ],
            'supervisor' => [
                "read-own-purchases",
                "read-all-reports",
                "read-all-products",
                "read-all-customers",
                "read-all-purchases"
            ],
            'editor' => [
                "read-own-purchases",
                "read-all-products",
                "edit-content"
            ],
            'cliente' => [
                "read-own-purchases",
                "read-own-addresses",
                "create-address",
                "update-address",
                "delete-address",
                "read-own-favorites",
                "create-favorites",
                "delete-favorites",
                "read-own-favorites-list",
                "create-favorites-list",
                "update-favorites-list",
                "read-all-products",
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
            $superadmin->givePermissionTo([
                "read-all-addresses",
                "create-address",
            ]);
        }

        $admin = User::find(2);
        if ($admin) {
            $admin->assignRole('admin');
        }

        $supervisor = User::find(3);
        if ($supervisor) {
            $supervisor->assignRole('supervisor');
            $supervisor->givePermissionTo([
                "read-all-addresses",

            ]);
        }

        $editor = User::find(4);
        if ($editor) {
            $editor->assignRole('editor');
        }

        $cliente = User::find(5);
        if ($cliente) {
            $cliente->assignRole('cliente');
            $cliente->givePermissionTo([
                "create-address",
                "update-address",
                "delete-address",
                "read-own-addresses",
                "read-own-purchases",
                "read-all-products",
            ]);
        }
    }
}
