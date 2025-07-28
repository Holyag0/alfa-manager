<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement(['phone', 'email']);
        
        return [
            'type' => $type,
            'value' => $type === 'email' ? $this->faker->unique()->safeEmail() : $this->faker->cellphoneNumber(),
            'label' => $this->faker->randomElement(['pessoal', 'trabalho', 'emergencia']),
            'is_primary' => false,
            'contact_for' => $this->faker->randomElement(['trabalho', 'pessoal', 'emergencia']),
        ];
    }

    /**
     * Indicate that the contact is primary.
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }

    /**
     * Indicate that the contact is an email.
     */
    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'email',
            'value' => $this->faker->unique()->safeEmail(),
        ]);
    }

    /**
     * Indicate that the contact is a phone.
     */
    public function phone(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'phone',
            'value' => $this->faker->cellphoneNumber(),
        ]);
    }

    /**
     * Indicate that the contact is for personal use.
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'label' => 'pessoal',
            'contact_for' => 'pessoal',
        ]);
    }

    /**
     * Indicate that the contact is for work.
     */
    public function work(): static
    {
        return $this->state(fn (array $attributes) => [
            'label' => 'trabalho',
            'contact_for' => 'trabalho',
        ]);
    }

    /**
     * Indicate that the contact is for emergency.
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'label' => 'emergencia',
            'contact_for' => 'emergencia',
        ]);
    }

    /**
     * Create a WhatsApp contact.
     */
    public function whatsapp(): static
    {
        return $this->phone()->personal()->state(fn (array $attributes) => [
            'label' => 'whatsapp',
        ]);
    }
} 