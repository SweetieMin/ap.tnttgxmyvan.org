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
        ->assertSee('Bảng điểm')
        ->assertSee($schedule->subjectName())
        ->assertSee($schedule->classroomName())
        ->assertSee('8.50')
        ->assertDontSee('Thiếu Nhi Khác');
});

test('dashboard shows average overview and subject summaries for the logged in youth', function () {
    Role::findOrCreate('thiếu nhi', 'web');

    $youth = User::factory()->create([
        'holy_name' => 'Maria',
        'name' => 'Thiếu Nhi Trung Bình',
    ]);
    $youth->assignRole('thiếu nhi');

    $catechismAssignment = ClassroomSubject::factory()->create();
    $catechismAssignment->classroom->update(['code' => 'GL01']);
    $catechismAssignment->subject->update(['name' => 'Giáo lý']);
    $catechismAssignment->classroom->youths()->sync([$youth->id]);

    $practiceAssignment = ClassroomSubject::factory()->create();
    $practiceAssignment->classroom->update(['code' => 'TH01']);
    $practiceAssignment->subject->update(['name' => 'Thực hành']);
    $practiceAssignment->classroom->youths()->sync([$youth->id]);

    $catechismScheduleOne = Schedule::factory()->for($catechismAssignment)->create([
        'date' => '2026-04-10',
        'start_time' => '17:00',
        'end_time' => '20:15',
    ]);

    $catechismScheduleTwo = Schedule::factory()->for($catechismAssignment)->create([
        'date' => '2026-04-17',
        'start_time' => '17:00',
        'end_time' => '20:15',
    ]);

    $practiceSchedule = Schedule::factory()->for($practiceAssignment)->create([
        'date' => '2026-04-24',
        'start_time' => '17:00',
        'end_time' => '20:15',
    ]);

    Score::query()->create([
        'schedule_id' => $catechismScheduleOne->id,
        'user_id' => $youth->id,
        'spirit_score' => 8,
        'theory_score' => 9,
        'practice_score' => 9,
    ]);

    Score::query()->create([
        'schedule_id' => $catechismScheduleTwo->id,
        'user_id' => $youth->id,
        'spirit_score' => 10,
        'theory_score' => 8,
        'practice_score' => 8,
    ]);

    Score::query()->create([
        'schedule_id' => $practiceSchedule->id,
        'user_id' => $youth->id,
        'spirit_score' => 6,
        'theory_score' => 7,
        'practice_score' => 7,
    ]);

    $this->actingAs($youth);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Điểm trung bình tổng quan')
        ->assertSee('Trung bình theo môn')
        ->assertSee('8.00')
        ->assertSee('1/2')
        ->assertSee('Giáo lý')
        ->assertSee('GL01')
        ->assertSee('8.75')
        ->assertSee('2/2 buổi đã có điểm')
        ->assertSee('Thực hành')
        ->assertSee('TH01')
        ->assertSee('6.50')
        ->assertSee('1/1 buổi đã có điểm');
});
