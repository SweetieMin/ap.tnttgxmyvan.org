<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\ClassroomSubject;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassroomSubject>
 */
class ClassroomSubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'classroom_id' => Classroom::factory(),
            'subject_id' => Subject::factory(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
