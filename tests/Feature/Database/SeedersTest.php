<?php

use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\ScheduleSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\UserSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('role and permission seeder creates expected roles and permissions', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $adminRole = Role::findByName('admin', 'web');
    $teacherRole = Role::findByName('giáo viên', 'web');
    $childRole = Role::findByName('thiếu nhi', 'web');

    expect(Permission::findByName('manage users', 'web'))->not->toBeNull();
    expect(Permission::findByName('manage roles', 'web'))->not->toBeNull();
    expect(Permission::findByName('manage permissions', 'web'))->not->toBeNull();
    expect(Permission::findByName('manage bible verses', 'web'))->not->toBeNull();
    expect(Permission::findByName('view bible verses', 'web'))->not->toBeNull();

    expect($adminRole->hasPermissionTo('manage users'))->toBeTrue();
    expect($adminRole->hasPermissionTo('manage bible verses'))->toBeTrue();
    expect($teacherRole->hasPermissionTo('manage bible verses'))->toBeTrue();
    expect($teacherRole->hasPermissionTo('view settings'))->toBeTrue();
    expect($childRole->hasPermissionTo('view bible verses'))->toBeTrue();
    expect($childRole->hasPermissionTo('manage users'))->toBeFalse();
});

test('user seeder creates default users and assigns expected roles', function () {
    $this->seed([
        RoleAndPermissionSeeder::class,
        UserSeeder::class,
    ]);

    $admin = User::query()->where('email', 'tnttgxmyvan@gmail.com')->firstOrFail();
    $teacher = User::query()->where('email', 'giaovien@example.com')->firstOrFail();
    $child = User::query()->where('username', 'MV08081159')->firstOrFail();

    expect($admin->username)->toBe('mv21081010');
    expect($admin->hasRole('admin'))->toBeTrue();
    expect($admin->can('manage permissions'))->toBeTrue();

    expect($teacher->username)->toBe('giaovien');
    expect($teacher->hasRole('giáo viên'))->toBeTrue();
    expect($teacher->can('manage bible verses'))->toBeTrue();

    expect($child->username)->toBe('MV08081159');
    expect($child->hasRole('thiếu nhi'))->toBeTrue();
    expect($child->can('view bible verses'))->toBeTrue();
    expect($child->can('manage users'))->toBeFalse();
});

test('subject and schedule seeders create subjects and schedules from training plan', function () {
    $this->seed([
        RoleAndPermissionSeeder::class,
        UserSeeder::class,
        SubjectSeeder::class,
        ScheduleSeeder::class,
    ]);

    expect(Subject::query()->count())->toBe(18);
    expect(Subject::query()->where('name', 'Morse')->exists())->toBeTrue();
    expect(Subject::query()->where('name', 'Đi trại')->exists())->toBeTrue();

    expect(Classroom::query()->where('code', 'TNTT-CB')->exists())->toBeTrue();
    expect(Schedule::query()->count())->toBe(count(ScheduleSeeder::scheduleItems()));
    expect(Schedule::query()->whereDate('date', '2026-06-13')->exists())->toBeTrue();
    expect(Schedule::query()->where('type', 'study')->count())->toBe(19);
    expect(Schedule::query()->where('type', 'exam')->count())->toBe(2);
    expect(Schedule::query()->where('type', 'camp')->count())->toBe(4);
    expect(Schedule::query()->where('type', 'reminder')->count())->toBe(1);
});
