<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'birth_date' => $this->faker->date('Y-m-d', '-6 years'),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'cpf' => $this->faker->unique()->numerify('###########'),
            'rg' => $this->faker->numerify('#########'),
            'birth_certificate_number' => $this->faker->numerify('#########'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
} 