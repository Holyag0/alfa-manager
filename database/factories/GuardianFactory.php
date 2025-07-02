<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GuardianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'relationship' => $this->faker->randomElement(['Pai', 'Mãe', 'Tio', 'Avô', 'Responsável']),
            'cpf' => $this->faker->unique()->numerify('###########'),
            'rg' => $this->faker->numerify('#########'),
            'marital_status' => $this->faker->randomElement(['solteiro', 'casado', 'divorciado']),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'guardian_type' => $this->faker->randomElement(['titular', 'financeiro', 'secundario']),
            'occupation' => $this->faker->jobTitle(),
            'workplace' => $this->faker->company(),
            'birth_date' => $this->faker->date('Y-m-d', '-25 years'),
            'gender' => $this->faker->randomElement(['M', 'F']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
} 