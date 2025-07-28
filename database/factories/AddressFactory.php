<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'zip_code' => $this->faker->numerify('#####-###'),
            'street' => $this->faker->streetName(),
            'number' => $this->faker->buildingNumber(),
            'complement' => $this->faker->optional()->randomElement(['Apto 101', 'Casa 2', 'Bloco A', 'Fundos']),
            'neighborhood' => $this->faker->citySuffix(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'address_for' => $this->faker->randomElement(['casa', 'trabalho', 'outro']),
            'is_primary' => false,
        ];
    }

    /**
     * Indicate that the address is primary.
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }

    /**
     * Indicate that the address is for home.
     */
    public function home(): static
    {
        return $this->state(fn (array $attributes) => [
            'address_for' => 'casa',
        ]);
    }

    /**
     * Indicate that the address is for work.
     */
    public function work(): static
    {
        return $this->state(fn (array $attributes) => [
            'address_for' => 'trabalho',
        ]);
    }

    /**
     * Create a complete São Paulo address.
     */
    public function saoPaulo(): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => 'São Paulo',
            'state' => 'SP',
            'neighborhood' => $this->faker->randomElement(['Centro', 'Vila Madalena', 'Moema', 'Pinheiros', 'Liberdade']),
            'zip_code' => $this->faker->randomElement(['01000-000', '01310-100', '04038-001', '05422-000', '01503-000']),
        ]);
    }
} 