<?php

namespace Database\Factories;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Lớp '.$this->faker->unique()->numerify('###'),
            'code' => Str::upper($this->faker->unique()->bothify('LH###')),
            'description' => $this->faker->optional()->sentence(),
            'start_date' => $this->faker->optional()->date(),
            'end_date' => $this->faker->optional()->date(),
            'status' => $this->faker->randomElement(['pending', 'open', 'closed']),
        ];
    }
}
