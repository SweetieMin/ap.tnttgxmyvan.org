<?php

use App\Livewire\Admin\Management\Classroom\Action as ClassroomAction;
use App\Livewire\Admin\Management\Classroom\ClassroomList;
use App\Models\Classroom;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('classroom action does not eager load youths during idle render', function () {
    Role::findOrCreate('thiếu nhi', 'web');
    Role::findOrCreate('giáo viên', 'web');

    $this->actingAs(User::factory()->create());

    $classroom = Classroom::factory()->create([
        'name' => 'Lớp Căn Bản 2026',
    ]);

    $component = Livewire::test(ClassroomAction::class, [
        'selectedClassroomId' => $classroom->id,
    ]);

    expect($component->instance()->selectedClassroomName)->toBeNull();
    expect($component->instance()->availableSubjects)->toHaveCount(0);
    expect($component->instance()->availableTeachers)->toHaveCount(0);
    expect($component->instance()->availableYouths)->toHaveCount(0);
});

test('opening youth modal loads classroom context and assigned youths', function () {
    Role::findOrCreate('thiếu nhi', 'web');

    $this->actingAs(User::factory()->create());

    $classroom = Classroom::factory()->create([
        'name' => 'Lớp Căn Bản 2026',
    ]);

    $youth = User::factory()->create();
    $youth->assignRole('thiếu nhi');
    $classroom->youths()->sync([$youth->id]);

    Livewire::test(ClassroomAction::class, [
        'selectedClassroomId' => $classroom->id,
    ])
        ->call('openYouthModal')
        ->assertSet('selectedClassroomName', 'Lớp Căn Bản 2026')
        ->assertSet('isYouthModalOpen', true)
        ->assertSet('youth_ids', [(string) $youth->id]);
});

test('classroom list opens youth modal for the classroom currently shown by default', function () {
    $classroom = Classroom::factory()->create([
        'name' => 'Lớp Căn Bản 2026',
    ]);

    Livewire::test(ClassroomList::class)
        ->call('openYouthModal')
        ->assertDispatched('open-youth-modal', id: $classroom->id);
});

test('opening youth modal accepts classroom id from list event payload', function () {
    Role::findOrCreate('thiếu nhi', 'web');

    $this->actingAs(User::factory()->create());

    $classroom = Classroom::factory()->create([
        'name' => 'Lớp Căn Bản 2026',
    ]);

    $youth = User::factory()->create();
    $youth->assignRole('thiếu nhi');
    $classroom->youths()->sync([$youth->id]);

    Livewire::test(ClassroomAction::class)
        ->call('openYouthModal', ['id' => $classroom->id])
        ->assertSet('selectedClassroomId', $classroom->id)
        ->assertSet('selectedClassroomName', 'Lớp Căn Bản 2026')
        ->assertSet('isYouthModalOpen', true)
        ->assertSet('youth_ids', [(string) $youth->id]);
});
