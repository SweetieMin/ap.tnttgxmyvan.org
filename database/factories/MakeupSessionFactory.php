<?php

namespace Database\Factories;

use App\Models\MakeupSession;
use App\Models\Subject;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MakeupSession>
 */
class MakeupSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = CarbonImmutable::today()->addDays($this->faker->numberBetween(0, 30));

        return [
            'subject_id' => Subject::factory(),
            'teacher_id' => User::factory(),
            'date' => $date->toDateString(),
            'start_time' => '19:00',
            'end_time' => '20:30',
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'resolved', 'closed']),
            'date_end_spirit' => $date->addDay()->toDateString(),
            'date_end_practice_theory' => $date->addDays(7)->toDateString(),
            'have_record' => true,
            'note' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
