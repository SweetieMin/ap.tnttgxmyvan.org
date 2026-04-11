<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionsByRole = [
            'admin' => [
                'access dashboard',
                'view settings',
                'manage users',
                'manage roles',
                'manage permissions',
                'manage bible verses',
                'personnel.teacher.view',
                'personnel.teacher.create',
                'personnel.teacher.update',
                'personnel.teacher.delete',
                'personnel.youth.view',
                'personnel.youth.create',
                'personnel.youth.update',
                'personnel.youth.delete',
            ],
            'giáo viên' => [
                'access dashboard',
                'view settings',
                'manage bible verses',
            ],
            'thiếu nhi' => [
                'access dashboard',
                'view bible verses',
            ],
        ];

        $permissions = collect($permissionsByRole)
            ->flatten()
            ->unique()
            ->mapWithKeys(fn (string $permission): array => [
                $permission => Permission::findOrCreate($permission, 'web'),
            ]);

        foreach ($permissionsByRole as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');

            $role->syncPermissions(
                collect($rolePermissions)
                    ->map(fn (string $permission) => $permissions[$permission])
                    ->all(),
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
