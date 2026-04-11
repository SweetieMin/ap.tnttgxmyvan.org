<?php

use App\Models\Classroom;
use App\Models\ClassroomSubject;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('authenticated users can manage classroom subjects from the classroom page', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $viewer = User::factory()->create();
    $teacher = User::factory()->create([
        'name' => 'Giáo viên phụ trách',
        'username' => 'giaovienphutrach',
    ]);
    $teacher->assignRole('giáo viên');
    $youth = User::factory()->create([
        'holy_name' => 'Maria',
        'name' => 'Thiếu nhi mẫu',
        'username' => 'MVTHIEUNHI01',
    ]);
    $youth->assignRole('thiếu nhi');

    $classroom = Classroom::factory()->create([
        'name' => 'Lớp Thiếu Nhi 1',
        'code' => 'TN1',
        'status' => 'open',
    ]);
    $subject = Subject::factory()->create([
        'name' => 'Giáo lý',
        'code' => 'GL01',
        'status' => 'active',
    ]);
    $assignment = ClassroomSubject::factory()->create([
        'classroom_id' => $classroom->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);
    $assignment->teachers()->sync([$teacher->id]);
    $classroom->youths()->sync([$youth->id]);

    $this->actingAs($viewer);

    $this->get(route('admin.management.classroom.index'))
        ->assertSuccessful()
        ->assertSeeText('Môn học của lớp')
        ->assertSeeText('Thiếu nhi của lớp')
        ->assertSeeText('Lớp Thiếu Nhi 1')
        ->assertSeeText('Giáo lý')
        ->assertSeeText('Giáo viên phụ trách')
        ->assertSeeText('Thiếu nhi mẫu')
        ->assertSeeText('Quản lý môn');
});

test('authenticated users can visit the subject management page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('admin.management.subject.index'))
        ->assertSuccessful()
        ->assertSeeText('Quản lý môn học')
        ->assertSeeText('Thêm môn học');
});
