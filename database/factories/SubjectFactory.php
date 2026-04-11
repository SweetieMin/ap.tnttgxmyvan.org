<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'code' => Str::upper($this->faker->unique()->bothify('MH###')),
            'description' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
