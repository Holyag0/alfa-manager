<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClassroomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['1º Ano A', '1º Ano B', '2º Ano A', '2º Ano B', '3º Ano']),
            'year' => $this->faker->year(),
            'shift' => $this->faker->randomElement(['Manhã', 'Tarde', 'Noite']),
            'vacancies' => $this->faker->numberBetween(20, 40),
        ];
    }
} 