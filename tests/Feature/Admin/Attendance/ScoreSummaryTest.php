<?php

use App\Livewire\Admin\Attendance\ScoreSummaryIndex;
use App\Models\Attendance;
use App\Models\AttendanceMakeup;
use App\Models\Classroom;
use App\Models\ClassroomSubject;
use App\Models\MakeupSession;
use App\Models\Schedule;
use App\Models\Score;
use App\Models\Subject;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    Permission::findOrCreate('attendance.view', 'web');
    Role::findOrCreate('thiếu nhi', 'web');
    Role::findOrCreate('giáo viên', 'web');
});

test('score summary page requires attendance view permission', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.attendance.score-summary.index'))
        ->assertForbidden();
});

test('score summary averages regular and makeup scores by subject', function () {
    $teacher = User::factory()->create(['name' => 'Teacher A']);
    $teacher->assignRole('giáo viên');

    $manager = User::factory()->create();
    $manager->givePermissionTo('attendance.view');

    $youth = User::factory()->create([
        'name' => 'Teresa Nguyen',
        'username' => 'MV001',
    ]);
    $youth->assignRole('thiếu nhi');

    $subject = Subject::factory()->create([
        'name' => 'Giáo lý',
        'status' => 'active',
    ]);
    $classroom = Classroom::factory()->create(['code' => 'CB26']);
    $classroom->youths()->attach($youth);

    $assignment = ClassroomSubject::factory()
        ->for($classroom)
        ->for($subject)
        ->create(['status' => 'active']);
    $assignment->teachers()->attach($teacher);

    $regularSchedule = Schedule::factory()->for($assignment)->create([
        'date' => now()->subDays(10)->toDateString(),
        'start_time' => '19:00',
        'end_time' => '20:30',
        'have_record' => true,
    ]);

    Score::query()->create([
        'schedule_id' => $regularSchedule->id,
        'user_id' => $youth->id,
        'spirit_score' => 8,
        'theory_score' => 8,
        'practice_score' => 8,
    ]);

    Attendance::factory()->create([
        'schedule_id' => $regularSchedule->id,
        'user_id' => $youth->id,
        'status' => Attendance::STATUS_ON_TIME,
        'marked_by' => $teacher->id,
    ]);

    $originalSchedule = Schedule::factory()->for($assignment)->create([
        'date' => now()->subDays(5)->toDateString(),
        'start_time' => '19:00',
        'end_time' => '20:30',
        'have_record' => true,
    ]);

    $originalAttendance = Attendance::factory()->create([
        'schedule_id' => $originalSchedule->id,
        'user_id' => $youth->id,
        'status' => Attendance::STATUS_ABSENT_EXCUSED,
    ]);

    $makeupSession = MakeupSession::factory()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => now()->subDay()->toDateString(),
        'start_time' => '19:00',
        'end_time' => '20:30',
        'have_record' => true,
    ]);

    AttendanceMakeup::query()->create([
        'original_attendance_id' => $originalAttendance->id,
        'makeup_session_id' => $makeupSession->id,
        'user_id' => $youth->id,
        'original_attendance_status' => Attendance::STATUS_ABSENT_EXCUSED,
        'status' => AttendanceMakeup::STATUS_COMPLETED,
        'attendance_status' => Attendance::STATUS_ON_TIME,
        'marked_by' => $teacher->id,
        'marked_at' => now(),
        'completed_at' => now(),
        'spirit_score' => 10,
        'theory_score' => 10,
        'practice_score' => 10,
        'final_score' => 10,
        'result_status' => Score::RESULT_PASSED,
    ]);

    $component = Livewire::actingAs($manager)
        ->test(ScoreSummaryIndex::class);

    $cell = $component->instance()->scoreMatrix->get($youth->id)->get($subject->id);

    expect($cell['average'])->toBe(9.0);
    expect($cell['entries_count'])->toBe(2);

    $component
        ->call('openDetail', $youth->id, $subject->id)
        ->assertSee('Teacher A')
        ->assertSee('Lịch học')
        ->assertSee('Lịch bù');
});

test('score summary subjects are ordered by earliest schedule date', function () {
    $manager = User::factory()->create();
    $manager->givePermissionTo('attendance.view');

    $youth = User::factory()->create();
    $youth->assignRole('thiếu nhi');

    $earlySubject = Subject::factory()->create(['name' => 'Z Môn đầu tiên']);
    $lateSubject = Subject::factory()->create(['name' => 'B Môn sau']);
    $classroom = Classroom::factory()->create();
    $classroom->youths()->attach($youth);

    $earlyAssignment = ClassroomSubject::factory()
        ->for($classroom)
        ->for($earlySubject)
        ->create();
    $lateAssignment = ClassroomSubject::factory()
        ->for($classroom)
        ->for($lateSubject)
        ->create();

    Schedule::factory()->for($earlyAssignment)->create([
        'date' => '2026-04-10',
        'have_record' => true,
    ]);
    $lateSchedule = Schedule::factory()->for($lateAssignment)->create([
        'date' => '2026-04-20',
        'have_record' => true,
    ]);

    Score::query()->create([
        'schedule_id' => $lateSchedule->id,
        'user_id' => $youth->id,
        'spirit_score' => 8,
        'theory_score' => 8,
        'practice_score' => 8,
    ]);

    $component = Livewire::actingAs($manager)->test(ScoreSummaryIndex::class);

    expect($component->instance()->subjects->pluck('id')->take(2)->all())
        ->toBe([$earlySubject->id, $lateSubject->id]);
});
