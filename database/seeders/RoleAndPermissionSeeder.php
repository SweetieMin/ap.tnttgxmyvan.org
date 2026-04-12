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

                'manage roles',
                'manage permissions',

                'personnel.teacher.view',
                'personnel.teacher.create',
                'personnel.teacher.update',
                'personnel.teacher.delete',

                'personnel.youth.view',
                'personnel.youth.create',
                'personnel.youth.update',
                'personnel.youth.delete',

                'management.schedule.view',
                'management.schedule.create',
                'management.schedule.update',
                'management.schedule.delete',

                'management.subject.view',
                'management.subject.create',
                'management.subject.update',
                'management.subject.delete',

                'management.classroom.view',
                'management.classroom.create',
                'management.classroom.update',
                'management.classroom.delete',

                'attendance.view',

            ],
            'giáo viên' => [
                'attendance.view',
            ],
            'thiếu nhi' => [
                'general.score.view',
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
