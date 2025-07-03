<?php

namespace Database\Factories;

use App\Models\Guardian;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement(['phone', 'email']);
        return [
            'guardian_id' => Guardian::factory(),
            'type' => $type,
            'value' => $type === 'email' ? $this->faker->unique()->safeEmail() : $this->faker->phoneNumber(),
            'label' => $this->faker->randomElement(['pessoal', 'trabalho', 'emergencia']),
            'is_primary' => $this->faker->boolean(80),
            'contact_for' => $this->faker->randomElement(['trabalho', 'pessoal', 'emergencia']),
        ];
    }
} 