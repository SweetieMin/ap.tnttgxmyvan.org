<?php

use App\Models\ClassroomSubject;
use App\Models\Schedule;
use App\Models\Score;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('dashboard shows only the logged in youth score table', function () {
    Role::findOrCreate('thiếu nhi', 'web');

    $youth = User::factory()->create([
        'holy_name' => 'Maria',
        'name' => 'Thiếu Nhi Mẫu',
    ]);
    $youth->assignRole('thiếu nhi');

    $otherYouth = User::factory()->create([
        'holy_name' => 'Phaolo',
        'name' => 'Thiếu Nhi Khác',
    ]);
    $otherYouth->assignRole('thiếu nhi');

    $assignment = ClassroomSubject::factory()->create();
    $assignment->classroom->youths()->sync([$youth->id, $otherYouth->id]);

    $schedule = Schedule::factory()->for($assignment)->create([
        'date' => '2026-04-11',
        'start_time' => '17:00',
        'end_time' => '20:15',
    ]);

    Score::query()->create([
        'schedule_id' => $schedule->id,
        'user_id' => $youth->id,
        'spirit_score' => 8,
        'theory_score' => 9,
        'practice_score' => 9,
    ]);

    Score::query()->create([
        'schedule_id' => $schedule->id,
        'user_id' => $otherYouth->id,
        'spirit_score' => 5,
        'theory_score' => 5,
        'practice_score' => 5,
    ]);

    $this->actingAs($youth);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Bảng điểm của con')
        ->assertSee('Maria Thiếu Nhi Mẫu')
        ->assertSee($schedule->subjectName())
        ->assertSee($schedule->classroomName())
        ->assertSee('8.50')
        ->assertDontSee('Thiếu Nhi Khác');
});
