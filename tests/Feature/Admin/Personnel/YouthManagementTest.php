<?php

use App\Livewire\Admin\Personnel\Youth\Action as YouthAction;
use App\Livewire\Admin\Personnel\Youth\YouthIndex;
use App\Livewire\Admin\Personnel\Youth\YouthList;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    collect([
        'personnel.youth.view',
        'personnel.youth.create',
        'personnel.youth.update',
        'personnel.youth.delete',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));

    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('giáo viên', 'web');
    Role::findOrCreate('thiếu nhi', 'web');
});

test('youth page requires the view permission', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/admin/personnel/youth')->assertForbidden();
});

test('youth page is displayed with the view permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('personnel.youth.view');

    $this->actingAs($user);

    $this->get('/admin/personnel/youth')
        ->assertOk()
        ->assertSee('Quản lý thiếu nhi');
});

test('authorized user can create update and delete a youth with role selection', function () {
    $manager = User::factory()->create();
    $manager->givePermissionTo([
        'personnel.youth.view',
        'personnel.youth.create',
        'personnel.youth.update',
        'personnel.youth.delete',
    ]);

    $this->actingAs($manager);

    Livewire::test(YouthAction::class)
        ->call('openCreateModal')
        ->set('holy_name', 'Thaõ')
        ->set('name', 'Tran Van A')
        ->set('birthday', '2010-01-02')
        ->set('username', 'Youth.User')
        ->set('email', 'youth@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('role', 'thiếu nhi')
        ->call('saveUser')
        ->assertHasNoErrors();

    $createdUser = User::query()->where('email', 'youth@example.com')->firstOrFail();

    expect($createdUser->username)->toBe('YOUTH.USER');
    expect($createdUser->hasRole('thiếu nhi'))->toBeTrue();

    Livewire::test(YouthAction::class)
        ->call('openEditModal', $createdUser->id)
        ->set('name', 'Tran Van B')
        ->set('role', 'admin')
        ->set('email', 'youth.updated@example.com')
        ->set('username', 'youth.updated')
        ->set('holy_name', 'Pio')
        ->call('saveUser')
        ->assertHasNoErrors();

    expect($createdUser->refresh()->name)->toBe('Tran Van B');
    expect($createdUser->email)->toBe('youth.updated@example.com');
    expect($createdUser->username)->toBe('YOUTH.UPDATED');
    expect($createdUser->hasRole('admin'))->toBeTrue();

    Livewire::test(YouthAction::class)
        ->call('openDeleteModal', $createdUser->id)
        ->call('deleteUser')
        ->assertHasNoErrors();

    $this->assertModelMissing($createdUser);
});

test('create button is hidden without the create permission', function () {
    $viewer = User::factory()->create();
    $viewer->givePermissionTo('personnel.youth.view');

    $this->actingAs($viewer);

    $this->get('/admin/personnel/youth')
        ->assertOk()
        ->assertDontSeeHtml('wire:click="openCreateModal"');
});

test('youth index only shows users with the thiếu nhi role', function () {
    $manager = User::factory()->create();
    $manager->givePermissionTo('personnel.youth.view');

    $youth = User::factory()->create(['email' => 'youth@example.com']);
    $youth->assignRole('thiếu nhi');

    $nonYouth = User::factory()->create(['email' => 'other@example.com']);
    $nonYouth->assignRole('admin');

    $this->actingAs($manager);

    Livewire::test(YouthList::class)
        ->assertSee('youth@example.com')
        ->assertDontSee('other@example.com');
});

test('youth index opens edit and delete modal states', function () {
    $manager = User::factory()->create();
    $manager->givePermissionTo([
        'personnel.youth.update',
        'personnel.youth.delete',
    ]);

    $youth = User::factory()->create();
    $youth->assignRole('thiếu nhi');

    $this->actingAs($manager);

    Livewire::test(YouthAction::class)
        ->call('openEditModal', $youth->id)
        ->assertSet('editingUserId', $youth->id)
        ->call('openDeleteModal', $youth->id)
        ->assertSet('deletingUserId', $youth->id);
});

test('youth index dispatches flux modal events', function () {
    $manager = User::factory()->create();
    $manager->givePermissionTo([
        'personnel.youth.create',
        'personnel.youth.delete',
    ]);

    $youth = User::factory()->create();

    $this->actingAs($manager);

    Livewire::test(YouthAction::class)
        ->call('openCreateModal')
        ->assertDispatched('modal-show')
        ->call('closeFormModal')
        ->assertDispatched('modal-close')
        ->call('openDeleteModal', $youth->id)
        ->assertDispatched('modal-show')
        ->call('closeDeleteModal')
        ->assertDispatched('modal-close');
});

test('youth form can load user data from main site by account code', function () {
    $manager = User::factory()->create();
    $manager->givePermissionTo([
        'personnel.youth.view',
        'personnel.youth.create',
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

    Livewire::test(YouthAction::class)
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
