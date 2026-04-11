<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    collect([
        'management.schedule.view',
        'management.classroom.view',
        'management.subject.view',
        'attendance.view',
        'personnel.teacher.view',
        'personnel.youth.view',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('sidebar hides protected groups when user has no related permissions', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertDontSeeText('Schedule Management')
        ->assertDontSeeText('Classroom Management')
        ->assertDontSeeText('Subject Management')
        ->assertDontSeeText('Attendance & Scoring')
        ->assertDontSeeText('Teacher Management')
        ->assertDontSeeText('Youth Management');
});

test('sidebar shows only the items allowed by the user permissions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'attendance.view',
        'personnel.teacher.view',
    ]);

    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('admin/attendance')
        ->assertSee('admin/personnel/teacher')
        ->assertDontSeeText('Schedule Management')
        ->assertDontSeeText('Classroom Management')
        ->assertDontSeeText('Subject Management')
        ->assertDontSeeText('Youth Management');
});

test('sidebar uses opposite collapsed desktop visibility classes for appearance controls', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('hidden in-data-flux-sidebar-collapsed-desktop:block', false)
        ->assertSee('in-data-flux-sidebar-collapsed-desktop:hidden', false);
});
