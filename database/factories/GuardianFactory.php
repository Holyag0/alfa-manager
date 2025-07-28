<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Contact;
use App\Models\Address;
use App\Models\Student;

class GuardianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'relationship' => $this->faker->randomElement(['pai', 'mae', 'avo', 'tio', 'tia', 'tutor']),
            'cpf' => $this->faker->unique()->numerify('###########'),
            'rg' => $this->faker->numerify('#########'),
            'marital_status' => $this->faker->randomElement(['solteiro', 'casado', 'divorciado', 'viuvo']),
            'status' => 'active',
            'guardian_type' => $this->faker->randomElement(['titular', 'suplente', 'financeiro', 'emergencia']),
            'occupation' => $this->faker->jobTitle(),
            'workplace' => $this->faker->company(),
            'birth_date' => $this->faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'gender' => $this->faker->randomElement(['masculino', 'feminino']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the guardian should have contacts.
     */
    public function withContacts(int $count = 2): static
    {
        return $this->afterCreating(function ($guardian) use ($count) {
            Contact::factory()
                ->count($count)
                ->for($guardian, 'contactable')
                ->create();
        });
    }

    /**
     * Indicate that the guardian should have addresses.
     */
    public function withAddresses(int $count = 1): static
    {
        return $this->afterCreating(function ($guardian) use ($count) {
            Address::factory()
                ->count($count)
                ->for($guardian, 'addressable')
                ->create();
        });
    }

    /**
     * Indicate that the guardian should have complete data.
     */
    public function complete(): static
    {
        return $this->withContacts()->withAddresses();
    }

    /**
     * Indicate that the guardian is titular (main guardian).
     */
    public function titular(): static
    {
        return $this->state(fn (array $attributes) => [
            'guardian_type' => 'titular',
        ]);
    }

    /**
     * Indicate that the guardian is suplente (backup guardian).
     */
    public function suplente(): static
    {
        return $this->state(fn (array $attributes) => [
            'guardian_type' => 'suplente',
        ]);
    }

    /**
     * Indicate that the guardian is financeiro (financial responsible).
     */
    public function financeiro(): static
    {
        return $this->state(fn (array $attributes) => [
            'guardian_type' => 'financeiro',
        ]);
    }

    /**
     * Indicate that the guardian is a father.
     */
    public function father(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship' => 'pai',
            'gender' => 'masculino',
        ]);
    }

    /**
     * Indicate that the guardian is a mother.
     */
    public function mother(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship' => 'mae',
            'gender' => 'feminino',
        ]);
    }

    /**
     * Attach this guardian to a student.
     */
    public function attachedTo(Student $student): static
    {
        return $this->afterCreating(function ($guardian) use ($student) {
            $student->guardians()->attach($guardian->id);
        });
    }
} 