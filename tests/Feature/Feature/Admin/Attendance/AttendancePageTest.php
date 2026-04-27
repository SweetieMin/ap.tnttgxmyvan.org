<?php

use App\Livewire\Admin\Attendance\AttendanceIndex;
use App\Models\Attendance;
use App\Models\ClassroomSubject;
use App\Models\Schedule;
use App\Models\Score;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

test('attendance page displays schedule roster in the attendance group', function () {
    $admin = User::factory()->create();
    Permission::findOrCreate('attendance.view', 'web');
    $admin->givePermissionTo('attendance.view');
    $youth = User::factory()->create([
        'holy_name' => 'Gioan',
        'name' => 'Thiếu Nhi A',
        'username' => 'thieunhia',
    ]);

    $assignment = ClassroomSubject::factory()->create();
    $assignment->classroom->youths()->attach($youth);

    Schedule::factory()->for($assignment)->create([
        'date' => now()->toDateString(),
        'start_time' => '19:00',
        'end_time' => '20:30',
        'have_record' => true,
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.attendance.index'));

    $response->assertOk()
        ->assertSee('Điểm danh và chấm điểm')
        ->assertSee('Thiếu Nhi A')
        ->assertSee('Nội quy điểm tinh thần')
        ->assertSee('2xl:grid-cols-[minmax(0,1.7fr)_minmax(320px,0.9fr)]', false)
        ->assertSee('min-w-0 overflow-hidden rounded-3xl', false)
        ->assertSee('overflow-x-auto', false);
});

test('attendance page stores spirit score from selected attendance status', function () {
    Carbon::setTestNow('2026-04-11 19:05:00');

    $admin = User::factory()->create();
    $youth = User::factory()->create();

    $assignment = ClassroomSubject::factory()->create();
    $assignment->classroom->youths()->attach($youth);
    $assignment->teachers()->attach($admin);

    $schedule = Schedule::factory()->for($assignment)->create([
        'date' => '2026-04-11',
        'start_time' => '19:00',
        'end_time' => '20:30',
        'have_record' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(AttendanceIndex::class)
        ->set('selectedScheduleId', $schedule->id)
        ->set('attendanceStatuses.'.$youth->id, Attendance::STATUS_LATE_EXCUSED)
        ->set('theoryScores.'.$youth->id, '8.50')
        ->set('practiceScores.'.$youth->id, '9.00')
        ->call('saveRecord', $youth->id);

    $this->assertDatabaseHas('attendances', [
        'schedule_id' => $schedule->id,
        'user_id' => $youth->id,
        'status' => Attendance::STATUS_LATE_EXCUSED,
    ]);

    $this->assertDatabaseHas('scores', [
        'schedule_id' => $schedule->id,
        'user_id' => $youth->id,
        'spirit_score' => 7.00,
        'theory_score' => 8.50,
        'practice_score' => 9.00,
        'result_status' => Score::RESULT_PASSED,
    ]);
});

test('attendance roster shows total score in the same request after saveRecord hydrates the roster', function () {
    Carbon::setTestNow('2026-04-11 19:05:00');

    $admin = User::factory()->create();
    $youth = User::factory()->create();

    $assignment = ClassroomSubject::factory()->create();
    $assignment->classroom->youths()->attach($youth);
    $assignment->teachers()->attach($admin);

    $schedule = Schedule::factory()->for($assignment)->create([
        'date' => '2026-04-11',
        'start_time' => '19:00',
        'end_time' => '20:30',
        'have_record' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(AttendanceIndex::class)
        ->set('selectedScheduleId', $schedule->id)
        ->set('attendanceStatuses.'.$youth->id, Attendance::STATUS_ON_TIME)
        ->set('theoryScores.'.$youth->id, '9')
        ->set('practiceScores.'.$youth->id, '9')
        ->call('saveRecord', $youth->id)
        ->assertSee('9.50');
});

test('attendance page accepts comma decimals for theory and practice scores', function () {
    Carbon::setTestNow('2026-04-11 19:05:00');

    $admin = User::factory()->create();
    $youth = User::factory()->create();

    $assignment = ClassroomSubject::factory()->create();
    $assignment->classroom->youths()->attach($youth);
    $assignment->teachers()->attach($admin);

    $schedule = Schedule::factory()->for($assignment)->create([
        'date' => '2026-04-11',
        'start_time' => '19:00',
        'end_time' => '20:30',
        'have_record' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(AttendanceIndex::class)
        ->set('selectedScheduleId', $schedule->id)
        ->set('attendanceStatuses.'.$youth->id, Attendance::STATUS_ON_TIME)
        ->set('theoryScores.'.$youth->id, '8,50')
        ->set('practiceScores.'.$youth->id, '9,00')
        ->call('saveRecord', $youth->id);

    $this->assertDatabaseHas('scores', [
        'schedule_id' => $schedule->id,
        'user_id' => $youth->id,
        'spirit_score' => 10.00,
        'theory_score' => 8.50,
        'practice_score' => 9.00,
    ]);
});

test('attendance page selects todays schedule by default', function () {
    Carbon::setTestNow('2026-04-23 10:00:00');

    $assignment = ClassroomSubject::factory()->create();

    Schedule::factory()->for($assignment)->create([
        'date' => '2026-04-20',
        'start_time' => '19:00',
        'have_record' => true,
    ]);

    $todaySchedule = Schedule::factory()->for($assignment)->create([
        'date' => '2026-04-23',
        'start_time' => '18:00',
        'have_record' => true,
    ]);

    Schedule::factory()->for($assignment)->create([
        'date' => '2026-04-24',
        'start_time' => '19:00',
        'have_record' => true,
    ]);

    $component = Livewire::test(AttendanceIndex::class);

    expect($component->instance()->selectedScheduleId)->toBe($todaySchedule->id);
});

test('attendance page selects the nearest upcoming schedule when today has no schedule', function () {
    Carbon::setTestNow('2026-04-23 10:00:00');

    $assignment = ClassroomSubject::factory()->create();

    Schedule::factory()->for($assignment)->create([
        'date' => '2026-04-20',
        'start_time' => '19:00',
        'have_record' => true,
    ]);

    $nearestUpcomingSchedule = Schedule::factory()->for($assignment)->create([
        'date' => '2026-04-24',
        'start_time' => '17:00',
        'have_record' => true,
    ]);

    Schedule::factory()->for($assignment)->create([
        'date' => '2026-04-30',
        'start_time' => '19:00',
        'have_record' => true,
    ]);

    $component = Livewire::test(AttendanceIndex::class);

    expect($component->instance()->selectedScheduleId)->toBe($nearestUpcomingSchedule->id);
});

test('my classes filter only shows schedules assigned to the authenticated teacher', function () {
    $teacher = User::factory()->create();

    $ownedAssignment = ClassroomSubject::factory()->create();
    $ownedAssignment->teachers()->attach($teacher);

    $otherAssignment = ClassroomSubject::factory()->create();

    $ownedSchedule = Schedule::factory()->for($ownedAssignment)->create(['have_record' => true]);
    Schedule::factory()->for($otherAssignment)->create(['have_record' => true]);

    $component = Livewire::actingAs($teacher)
        ->test(AttendanceIndex::class)
        ->set('scheduleScope', 'mine');

    expect($component->instance()->scheduleOptions->pluck('id')->all())->toBe([$ownedSchedule->id]);
});

test('teacher can only manage attendance after the assigned schedule has started', function () {
    Carbon::setTestNow('2026-04-11 18:30:00');

    $teacher = User::factory()->create();
    $assignment = ClassroomSubject::factory()->create();
    $assignment->teachers()->attach($teacher);

    $schedule = Schedule::factory()->for($assignment)->create([
        'date' => '2026-04-11',
        'start_time' => '19:00',
        'have_record' => true,
    ]);

    $component = Livewire::actingAs($teacher)
        ->test(AttendanceIndex::class)
        ->set('selectedScheduleId', $schedule->id);

    expect($component->instance()->canManageSelectedSchedule)->toBeFalse();

    Carbon::setTestNow('2026-04-11 19:00:00');

    $component = Livewire::actingAs($teacher)
        ->test(AttendanceIndex::class)
        ->set('selectedScheduleId', $schedule->id);

    expect($component->instance()->canManageSelectedSchedule)->toBeTrue();
});

test('attendance page only loads classroom once for the selected schedule roster', function () {
    $teacher = User::factory()->create();
    $youth = User::factory()->create();

    $assignment = ClassroomSubject::factory()->create();
    $assignment->teachers()->attach($teacher);
    $assignment->classroom->youths()->attach($youth);

    $schedule = Schedule::factory()->for($assignment)->create([
        'date' => now()->toDateString(),
        'have_record' => true,
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $component = Livewire::actingAs($teacher)
        ->test(AttendanceIndex::class)
        ->set('selectedScheduleId', $schedule->id);

    $component->instance()->rosterRows;

    $classroomQueries = collect(DB::getQueryLog())
        ->pluck('query')
        ->filter(fn (string $query): bool => str_contains($query, 'from `classrooms` where `classrooms`.`id` in'))
        ->values();

    expect($classroomQueries->count())->toBeLessThanOrEqual(1);
});
