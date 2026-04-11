<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schedule_id' => Schedule::factory(),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement([
                'on_time',
                'late_excused',
                'late_unexcused',
                'absent_excused',
                'absent_unexcused',
                'makeup_completed',
            ]),
            'note' => $this->faker->optional()->sentence(),
            'marked_by' => User::factory(),
            'marked_at' => $this->faker->optional()->dateTime(),
            'makeup_completed_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
