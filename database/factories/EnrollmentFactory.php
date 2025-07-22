<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Classroom;

class EnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'guardian_id' => Guardian::factory(),
            'classroom_id' => Classroom::factory(),
            'status' => $this->faker->randomElement(['active', 'pending', 'cancelled']),
            'enrollment_date' => $this->faker->date('Y-m-d'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
} 