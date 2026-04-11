<?php

use App\Livewire\Admin\Personnel\Teacher\Action as TeacherAction;
use App\Livewire\Admin\Personnel\Teacher\TeacherIndex;
use App\Livewire\Admin\Personnel\Teacher\TeacherList;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    collect([
        'personnel.teacher.view',
        'personnel.teacher.create',
        'personnel.teacher.update',
        'personnel.teacher.delete',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));

    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('giáo viên', 'web');
    Role::findOrCreate('thiếu nhi', 'web');
});

test('teacher page requires the view permission', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/admin/personnel/teacher')->assertForbidden();
});

test('teacher page is displayed with the view permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('personnel.teacher.view');

    $this->actingAs($user);

    $this->get('/admin/personnel/teacher')
        ->assertOk()
        ->assertSee('Quản lý giáo viên');
});

test('authorized user can create update and delete a user with role selection', function () {
    $manager = User::factory()->create();
    $manager->givePermissionTo([
        'personnel.teacher.view',
        'personnel.teacher.create',
        'personnel.teacher.update',
        'personnel.teacher.delete',
    ]);

    $this->actingAs($manager);

    Livewire::test(TeacherAction::class)
        ->call('openCreateModal')
        ->set('holy_name', 'Giuse')
        ->set('name', 'Nguyen Van A')
        ->set('birthday', '2010-01-02')
        ->set('username', 'Teacher.User')
        ->set('email', 'teacher@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('role', 'giáo viên')
        ->call('saveUser')
        ->assertHasNoErrors();

    $createdUser = User::query()->where('email', 'teacher@example.com')->firstOrFail();

    expect($createdUser->username)->toBe('TEACHER.USER');
    expect($createdUser->hasRole('giáo viên'))->toBeTrue();

    Livewire::test(TeacherAction::class)
        ->call('openEditModal', $createdUser->id)
        ->set('name', 'Nguyen Van B')
        ->set('role', 'admin')
        ->set('email', 'teacher.updated@example.com')
        ->set('username', 'teacher.updated')
        ->set('holy_name', 'Maria')
        ->call('saveUser')
        ->assertHasNoErrors();

    expect($createdUser->refresh()->name)->toBe('Nguyen Van B');
    expect($createdUser->email)->toBe('teacher.updated@example.com');
    expect($createdUser->username)->toBe('TEACHER.UPDATED');
    expect($createdUser->hasRole('admin'))->toBeTrue();

    Livewire::test(TeacherAction::class)
        ->call('openDeleteModal', $createdUser->id)
        ->call('deleteUser')
        ->assertHasNoErrors();

    $this->assertModelMissing($createdUser);
});

test('create button is hidden without the create permission', function () {
    $viewer = User::factory()->create();
    $viewer->givePermissionTo('personnel.teacher.view');

    $this->actingAs($viewer);

    $this->get('/admin/personnel/teacher')
        ->assertOk()
        ->assertDontSeeHtml('wire:click="openCreateModal"');
});

test('teacher index only shows users with the giáo viên role', function () {
    $manager = User::factory()->create();
    $manager->givePermissionTo('personnel.teacher.view');

    $teacher = User::factory()->create(['email' => 'teacher@example.com']);
    $teacher->assignRole('giáo viên');

    $nonTeacher = User::factory()->create(['email' => 'other@example.com']);
    $nonTeacher->assignRole('admin');

    $this->actingAs($manager);

    Livewire::test(TeacherList::class)
        ->assertSee('teacher@example.com')
        ->assertDontSee('other@example.com');
});

test('teacher index opens edit and delete modal states', function () {
    $manager = User::factory()->create();
    $manager->givePermissionTo([
        'personnel.teacher.update',
        'personnel.teacher.delete',
    ]);

    $teacher = User::factory()->create();
    $teacher->assignRole('giáo viên');

    $this->actingAs($manager);

    Livewire::test(TeacherAction::class)
        ->call('openEditModal', $teacher->id)
        ->assertSet('editingUserId', $teacher->id)
        ->call('openDeleteModal', $teacher->id)
        ->assertSet('deletingUserId', $teacher->id);
});

test('teacher index dispatches flux modal events', function () {
    $manager = User::factory()->create();
    $manager->givePermissionTo([
        'personnel.teacher.create',
        'personnel.teacher.delete',
    ]);

    $teacher = User::factory()->create();

    $this->actingAs($manager);

    Livewire::test(TeacherAction::class)
        ->call('openCreateModal')
        ->assertDispatched('modal-show')
        ->call('closeFormModal')
        ->assertDispatched('modal-close')
        ->call('openDeleteModal', $teacher->id)
        ->assertDispatched('modal-show')
        ->call('closeDeleteModal')
        ->assertDispatched('modal-close');
});

test('teacher form can load user data from main site by account code', function () {
    $manager = User::factory()->create();
    $manager->givePermissionTo([
        'personnel.teacher.view',
        'personnel.teacher.create',
    ]);

    $this->actingAs($manager);

    Http::fake([
        'https://tnttgxmyvan.org/api/users/by-account-code/MV19019797' => Http::response([
            'success' => true,
            'data' => [
                'id' => 2,
                'holy_name' => 'Toma',
                'name' => 'Nguyen Khac Huan',
                'email' => 'nguyenkhachuan1997@gmail.com',
                'username' => 'MV19019797',
                'birthday' => '19/01/1997',
                'phone' => '0868191110',
            ],
        ]),
    ]);

    Livewire::test(TeacherAction::class)
        ->call('openCreateModal')
        ->set('accountSource', 'account_code')
        ->set('accountCode', 'mv19019797')
        ->call('fetchUserByAccountCode')
        ->assertSet('accountCode', 'MV19019797')
        ->assertSet('holy_name', 'Toma')
        ->assertSet('name', 'Nguyen Khac Huan')
        ->assertSet('email', 'nguyenkhachuan1997@gmail.com')
        ->assertSet('username', 'MV19019797')
        ->assertSet('birthday', '1997-01-19')
        ->assertHasNoErrors(['accountCode']);
});
