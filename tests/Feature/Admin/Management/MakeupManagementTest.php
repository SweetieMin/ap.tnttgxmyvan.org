<?php

use App\Livewire\Admin\Management\Makeup\MakeupIndex;
use App\Models\Attendance;
use App\Models\AttendanceMakeup;
use App\Models\Classroom;
use App\Models\ClassroomSubject;
use App\Models\MakeupSession;
use App\Models\Schedule;
use App\Models\Score;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    collect([
        'management.schedule.view',
        'management.schedule.create',
        'management.schedule.update',
        'attendance.view',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));

    Role::findOrCreate('giáo viên', 'web');
});

test('makeup page is displayed with the management schedule view permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('management.schedule.view');

    $this->actingAs($user)
        ->get(route('admin.attendance.makeup.index'))
        ->assertOk()
        ->assertSee('Quản lý lịch bù')
        ->assertSee('Chưa có lịch bù nào');
});

test('manager can create a makeup session and assign eligible absences across different classrooms', function () {
    $manager = User::factory()->create();
    $manager->givePermissionTo([
        'management.schedule.view',
        'management.schedule.create',
    ]);

    $teacher = User::factory()->create();
    $teacher->assignRole('giáo viên');

    $subject = Subject::factory()->create(['status' => 'active']);
    $classroomOne = Classroom::factory()->create();
    $classroomTwo = Classroom::factory()->create();

    $assignmentOne = ClassroomSubject::factory()->for($classroomOne)->for($subject)->create(['status' => 'active']);
    $assignmentTwo = ClassroomSubject::factory()->for($classroomTwo)->for($subject)->create(['status' => 'active']);
    $assignmentOne->teachers()->attach($teacher);
    $assignmentTwo->teachers()->attach($teacher);

    $youthOne = User::factory()->create();
    $youthTwo = User::factory()->create();
    $classroomOne->youths()->attach($youthOne);
    $classroomTwo->youths()->attach($youthTwo);

    $scheduleOne = Schedule::factory()->for($assignmentOne)->create([
        'date' => '2026-04-10',
        'start_time' => '19:00',
        'end_time' => '20:30',
        'have_record' => true,
    ]);

    $scheduleTwo = Schedule::factory()->for($assignmentTwo)->create([
        'date' => '2026-04-11',
        'start_time' => '19:00',
        'end_time' => '20:30',
        'have_record' => true,
    ]);

    $attendanceOne = Attendance::factory()->create([
        'schedule_id' => $scheduleOne->id,
        'user_id' => $youthOne->id,
        'status' => Attendance::STATUS_ABSENT_EXCUSED,
    ]);

    $attendanceTwo = Attendance::factory()->create([
        'schedule_id' => $scheduleTwo->id,
        'user_id' => $youthTwo->id,
        'status' => Attendance::STATUS_ABSENT_UNEXCUSED,
    ]);

    $component = Livewire::actingAs($manager)
        ->test(MakeupIndex::class)
        ->call('openCreateModal')
        ->set('subject_id', $subject->id)
        ->set('teacher_id', $teacher->id)
        ->set('date', '2026-04-20')
        ->set('start_time', '19:00')
        ->set('end_time', '20:30')
        ->set('status', MakeupSession::STATUS_PENDING)
        ->call('saveSession')
        ->assertHasNoErrors();

    $session = MakeupSession::query()->firstOrFail();

    expect($component->instance()->eligibleOriginalAttendances->pluck('id')->all())
        ->toEqualCanonicalizing([$attendanceOne->id, $attendanceTwo->id]);

    $component
        ->set('selectedOriginalAttendanceIds', [$attendanceOne->id, $attendanceTwo->id])
        ->call('saveAssignments')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('attendance_makeups', [
        'original_attendance_id' => $attendanceOne->id,
        'makeup_session_id' => $session->id,
        'user_id' => $youthOne->id,
        'status' => AttendanceMakeup::STATUS_SCHEDULED,
    ]);

    $this->assertDatabaseHas('attendance_makeups', [
        'original_attendance_id' => $attendanceTwo->id,
        'makeup_session_id' => $session->id,
        'user_id' => $youthTwo->id,
        'status' => AttendanceMakeup::STATUS_SCHEDULED,
    ]);
});

test('manager can not create a makeup session when the teacher already has a conflicting regular schedule', function () {
    $manager = User::factory()->create();
    $manager->givePermissionTo([
        'management.schedule.view',
        'management.schedule.create',
    ]);

    $teacher = User::factory()->create();
    $teacher->assignRole('giáo viên');

    $subject = Subject::factory()->create(['status' => 'active']);
    $assignment = ClassroomSubject::factory()->for($subject)->create(['status' => 'active']);
    $assignment->teachers()->attach($teacher);

    Schedule::factory()->for($assignment)->create([
        'date' => '2026-04-20',
        'start_time' => '19:00',
        'end_time' => '20:30',
        'have_record' => true,
    ]);

    Livewire::actingAs($manager)
        ->test(MakeupIndex::class)
        ->call('openCreateModal')
        ->set('subject_id', $subject->id)
        ->set('teacher_id', $teacher->id)
        ->set('date', '2026-04-20')
        ->set('start_time', '19:15')
        ->set('end_time', '20:15')
        ->set('status', MakeupSession::STATUS_PENDING)
        ->call('saveSession')
        ->assertHasErrors(['teacher_id']);
});

test('teacher can save a completed makeup attendance and the original absence becomes makeup completed', function () {
    Carbon::setTestNow('2026-04-20 19:05:00');

    $teacher = User::factory()->create();
    $teacher->assignRole('giáo viên');
    $teacher->givePermissionTo('attendance.view');

    $subject = Subject::factory()->create(['status' => 'active']);
    $classroom = Classroom::factory()->create();
    $assignment = ClassroomSubject::factory()->for($classroom)->for($subject)->create(['status' => 'active']);
    $assignment->teachers()->attach($teacher);

    $youth = User::factory()->create();
    $classroom->youths()->attach($youth);

    $originalSchedule = Schedule::factory()->for($assignment)->create([
        'date' => '2026-04-10',
        'start_time' => '19:00',
        'end_time' => '20:30',
        'have_record' => true,
    ]);

    $originalAttendance = Attendance::factory()->create([
        'schedule_id' => $originalSchedule->id,
        'user_id' => $youth->id,
        'status' => Attendance::STATUS_ABSENT_EXCUSED,
    ]);

    $session = MakeupSession::factory()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => '2026-04-20',
        'start_time' => '19:00',
        'end_time' => '20:30',
        'have_record' => true,
    ]);

    $attendanceMakeup = AttendanceMakeup::query()->create([
        'original_attendance_id' => $originalAttendance->id,
        'makeup_session_id' => $session->id,
        'user_id' => $youth->id,
        'original_attendance_status' => Attendance::STATUS_ABSENT_EXCUSED,
        'status' => AttendanceMakeup::STATUS_SCHEDULED,
        'assigned_by' => $teacher->id,
        'assigned_at' => now(),
    ]);

    Livewire::actingAs($teacher)
        ->test(MakeupIndex::class)
        ->set('selectedSessionId', $session->id)
        ->set('attendanceStatuses.'.$attendanceMakeup->id, Attendance::STATUS_ON_TIME)
        ->set('theoryScores.'.$attendanceMakeup->id, '9')
        ->set('practiceScores.'.$attendanceMakeup->id, '9')
        ->call('saveMakeupRecord', $attendanceMakeup->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('attendance_makeups', [
        'id' => $attendanceMakeup->id,
        'status' => AttendanceMakeup::STATUS_COMPLETED,
        'attendance_status' => Attendance::STATUS_ON_TIME,
        'spirit_score' => 10.00,
        'theory_score' => 9.00,
        'practice_score' => 9.00,
        'result_status' => Score::RESULT_PASSED,
    ]);

    $this->assertDatabaseHas('attendances', [
        'id' => $originalAttendance->id,
        'status' => Attendance::STATUS_MAKEUP_COMPLETED,
    ]);

    expect($originalAttendance->refresh()->makeup_completed_at)->not->toBeNull();
});

test('teacher can save a missed makeup attendance and the original absence remains unchanged', function () {
    Carbon::setTestNow('2026-04-20 19:05:00');

    $teacher = User::factory()->create();
    $teacher->assignRole('giáo viên');
    $teacher->givePermissionTo('attendance.view');

    $subject = Subject::factory()->create(['status' => 'active']);
    $classroom = Classroom::factory()->create();
    $assignment = ClassroomSubject::factory()->for($classroom)->for($subject)->create(['status' => 'active']);
    $assignment->teachers()->attach($teacher);

    $youth = User::factory()->create();
    $classroom->youths()->attach($youth);

    $originalSchedule = Schedule::factory()->for($assignment)->create([
        'date' => '2026-04-10',
        'start_time' => '19:00',
        'end_time' => '20:30',
        'have_record' => true,
    ]);

    $originalAttendance = Attendance::factory()->create([
        'schedule_id' => $originalSchedule->id,
        'user_id' => $youth->id,
        'status' => Attendance::STATUS_ABSENT_UNEXCUSED,
    ]);

    $session = MakeupSession::factory()->create([
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'date' => '2026-04-20',
        'start_time' => '19:00',
        'end_time' => '20:30',
        'have_record' => true,
    ]);

    $attendanceMakeup = AttendanceMakeup::query()->create([
        'original_attendance_id' => $originalAttendance->id,
        'makeup_session_id' => $session->id,
        'user_id' => $youth->id,
        'original_attendance_status' => Attendance::STATUS_ABSENT_UNEXCUSED,
        'status' => AttendanceMakeup::STATUS_SCHEDULED,
        'assigned_by' => $teacher->id,
        'assigned_at' => now(),
    ]);

    Livewire::actingAs($teacher)
        ->test(MakeupIndex::class)
        ->set('selectedSessionId', $session->id)
        ->set('attendanceStatuses.'.$attendanceMakeup->id, Attendance::STATUS_ABSENT_EXCUSED)
        ->set('theoryScores.'.$attendanceMakeup->id, '0')
        ->set('practiceScores.'.$attendanceMakeup->id, '0')
        ->call('saveMakeupRecord', $attendanceMakeup->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('attendance_makeups', [
        'id' => $attendanceMakeup->id,
        'status' => AttendanceMakeup::STATUS_MISSED,
        'attendance_status' => Attendance::STATUS_ABSENT_EXCUSED,
        'spirit_score' => 0.00,
        'theory_score' => 0.00,
        'practice_score' => 0.00,
        'result_status' => Score::RESULT_FAILED,
    ]);

    $this->assertDatabaseHas('attendances', [
        'id' => $originalAttendance->id,
        'status' => Attendance::STATUS_ABSENT_UNEXCUSED,
    ]);

    expect($originalAttendance->refresh()->makeup_completed_at)->toBeNull();
});
