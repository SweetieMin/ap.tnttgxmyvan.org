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
            'status' => $this->faker->randomElement(['pending', 'present', 'late', 'absent', 'excused']),
            'note' => $this->faker->optional()->sentence(),
            'marked_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
