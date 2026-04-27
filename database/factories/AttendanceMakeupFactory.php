<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\AttendanceMakeup;
use App\Models\MakeupSession;
use App\Models\Score;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceMakeup>
 */
class AttendanceMakeupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'original_attendance_id' => Attendance::factory(),
            'makeup_session_id' => MakeupSession::factory(),
            'user_id' => User::factory(),
            'original_attendance_status' => Attendance::STATUS_ABSENT_EXCUSED,
            'status' => $this->faker->randomElement(['scheduled', 'completed', 'missed']),
            'attendance_status' => $this->faker->optional()->randomElement([
                Attendance::STATUS_ON_TIME,
                Attendance::STATUS_LATE_EXCUSED,
                Attendance::STATUS_LATE_UNEXCUSED,
                Attendance::STATUS_ABSENT_EXCUSED,
                Attendance::STATUS_ABSENT_UNEXCUSED,
            ]),
            'attendance_note' => $this->faker->optional()->sentence(),
            'assigned_by' => User::factory(),
            'assigned_at' => now(),
            'marked_by' => User::factory(),
            'marked_at' => $this->faker->optional()->dateTime(),
            'completed_at' => $this->faker->optional()->dateTime(),
            'spirit_score' => $this->faker->optional()->randomFloat(2, 0, 10),
            'theory_score' => $this->faker->optional()->randomFloat(2, 0, 10),
            'practice_score' => $this->faker->optional()->randomFloat(2, 0, 10),
            'final_score' => $this->faker->optional()->randomFloat(2, 0, 10),
            'result_status' => $this->faker->randomElement([
                Score::RESULT_PENDING,
                Score::RESULT_PASSED,
                Score::RESULT_FAILED,
            ]),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
