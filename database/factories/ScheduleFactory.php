<?php

namespace Database\Factories;

use App\Models\ClassroomSubject;
use App\Models\Schedule;
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
        return [
            'classroom_subject_id' => ClassroomSubject::factory(),
            'date' => $this->faker->date(),
            'start_time' => '08:00',
            'end_time' => '09:30',
            'type' => $this->faker->randomElement(['study', 'exam', 'camp', 'reminder']),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'resolved', 'closed']),
            'allow_attendance' => $this->faker->boolean(),
        ];
    }
}
