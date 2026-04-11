<?php

namespace Database\Factories;

use App\Models\ClassroomSubject;
use App\Models\Schedule;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = CarbonImmutable::today()->addDays($this->faker->numberBetween(0, 30));
        $startTime = '08:00';
        $endTime = '09:30';

        return [
            'classroom_subject_id' => ClassroomSubject::factory(),
            'date' => $date->toDateString(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'type' => $this->faker->randomElement(['study', 'exam', 'camp', 'reminder']),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'resolved', 'closed']),
            'date_end_spirit' => $date->addDay()->toDateString(),
            'date_end_practice_theory' => $date->addDays(7)->toDateString(),
            'have_record' => true,
        ];
    }
}
