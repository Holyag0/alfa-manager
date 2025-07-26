<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Contact;
use App\Models\Address;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'birth_date' => $this->faker->dateTimeBetween('-18 years', '-6 years')->format('Y-m-d'),
            'cpf' => $this->faker->unique()->numerify('###########'),
            'rg' => $this->faker->numerify('#########'),
            'birth_certificate_number' => $this->faker->numerify('#########'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the student should have contacts.
     */
    public function withContacts(int $count = 2): static
    {
        return $this->afterCreating(function ($student) use ($count) {
            Contact::factory()
                ->count($count)
                ->for($student, 'contactable')
                ->create();
        });
    }

    /**
     * Indicate that the student should have addresses.
     */
    public function withAddresses(int $count = 1): static
    {
        return $this->afterCreating(function ($student) use ($count) {
            Address::factory()
                ->count($count)
                ->for($student, 'addressable')
                ->create();
        });
    }

    /**
     * Indicate that the student should have complete data.
     */
    public function complete(): static
    {
        return $this->withContacts()->withAddresses();
    }

    /**
     * Indicate that the student is a minor (under 18).
     */
    public function minor(): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => $this->faker->dateTimeBetween('-17 years', '-6 years')->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the student is an adult (18+).
     */
    public function adult(): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => $this->faker->dateTimeBetween('-25 years', '-18 years')->format('Y-m-d'),
        ]);
    }
} 