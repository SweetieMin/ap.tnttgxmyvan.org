<?php

use App\Models\ClassroomSubject;
use App\Models\Schedule;
use App\Models\Score;
use App\Models\User;
use Illuminate\Support\Carbon;

test('it calculates the final score, stores result status, and writes audit history', function () {
    Carbon::setTestNow('2026-04-11 18:00:00');

    $teacher = User::factory()->create();
    $youth = User::factory()->create();
    $schedule = Schedule::factory()->for(ClassroomSubject::factory())->create([
        'have_record' => true,
        'start_time' => '19:00',
        'date_end_spirit' => Carbon::now()->toDateString(),
        'date_end_practice_theory' => Carbon::now()->toDateString(),
    ]);

    $this->actingAs($teacher);

    $score = Score::query()->create([
        'schedule_id' => $schedule->id,
        'user_id' => $youth->id,
        'spirit_score' => 8,
        'theory_score' => 9,
        'practice_score' => 7,
    ]);

    expect((float) $score->fresh()->final_score)->toBe(8.0)
        ->and($score->fresh()->result_status)->toBe(Score::RESULT_PASSED)
        ->and($score->fresh()->spirit_updated_by)->toBe($teacher->id)
        ->and($score->fresh()->theory_updated_by)->toBe($teacher->id)
        ->and($score->fresh()->practice_updated_by)->toBe($teacher->id)
        ->and($score->histories()->count())->toBe(5);

    $historyFields = $score->histories()->pluck('field_name')->all();

    expect($historyFields)->toEqualCanonicalizing([
        'spirit_score',
        'theory_score',
        'practice_score',
        'final_score',
        'result_status',
    ]);
});

test('it keeps pending status when one of the score columns is missing', function () {
    Carbon::setTestNow('2026-04-11 18:00:00');

    $teacher = User::factory()->create();
    $youth = User::factory()->create();
    $schedule = Schedule::factory()->for(ClassroomSubject::factory())->create([
        'start_time' => '19:00',
        'date_end_spirit' => Carbon::now()->toDateString(),
        'date_end_practice_theory' => Carbon::now()->toDateString(),
    ]);

    $this->actingAs($teacher);

    $score = Score::query()->create([
        'schedule_id' => $schedule->id,
        'user_id' => $youth->id,
        'spirit_score' => 10,
        'theory_score' => 8,
        'practice_score' => null,
    ]);

    expect($score->fresh()->final_score)->toBeNull()
        ->and($score->fresh()->result_status)->toBe(Score::RESULT_PENDING);
});

test('it prevents updating spirit score after the spirit deadline', function () {
    Carbon::setTestNow('2026-04-11 20:00:00');

    $teacher = User::factory()->create();
    $youth = User::factory()->create();
    $schedule = Schedule::factory()->for(ClassroomSubject::factory())->create([
        'have_record' => true,
        'start_time' => '19:59',
        'date_end_spirit' => Carbon::now()->toDateString(),
        'date_end_practice_theory' => Carbon::now()->addDay()->toDateString(),
    ]);

    $this->actingAs($teacher);

    expect(function () use ($schedule, $youth): void {
        Score::query()->create([
            'schedule_id' => $schedule->id,
            'user_id' => $youth->id,
            'spirit_score' => 9,
            'theory_score' => 9,
            'practice_score' => 9,
        ]);
    })->toThrow(DomainException::class);
});

test('it prevents updating theory or practice score after the theory practice deadline', function () {
    Carbon::setTestNow('2026-04-11 20:00:00');

    $teacher = User::factory()->create();
    $youth = User::factory()->create();
    $schedule = Schedule::factory()->for(ClassroomSubject::factory())->create([
        'have_record' => true,
        'start_time' => '19:59',
        'date_end_spirit' => Carbon::now()->addDay()->toDateString(),
        'date_end_practice_theory' => Carbon::now()->toDateString(),
    ]);

    $this->actingAs($teacher);

    expect(function () use ($schedule, $youth): void {
        Score::query()->create([
            'schedule_id' => $schedule->id,
            'user_id' => $youth->id,
            'spirit_score' => 9,
            'theory_score' => 9,
            'practice_score' => 9,
        ]);
    })->toThrow(DomainException::class);
});
