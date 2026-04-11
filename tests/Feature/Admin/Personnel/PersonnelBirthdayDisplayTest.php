<?php

use App\Livewire\Admin\Personnel\Teacher\TeacherIndex;
use App\Livewire\Admin\Personnel\Youth\YouthIndex;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('giáo viên', 'web');
    Role::findOrCreate('thiếu nhi', 'web');
});

test('teacher list formats birthday without crashing', function () {
    $teacher = User::factory()->create([
        'birthday' => '2010-01-02',
    ]);

    $teacher->assignRole('giáo viên');

    Livewire::test(TeacherIndex::class)
        ->assertSee('02/01/2010');
});

test('youth list formats birthday without crashing', function () {
    $youth = User::factory()->create([
        'birthday' => '2012-03-04',
    ]);

    $youth->assignRole('thiếu nhi');

    Livewire::test(YouthIndex::class)
        ->assertSee('04/03/2012');
});
