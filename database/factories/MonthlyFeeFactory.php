<?php

namespace Database\Factories;

use App\Models\MonthlyFee;
use App\Models\Enrollment;
use App\Models\ClassroomService;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonthlyFeeFactory extends Factory
{
    protected $model = MonthlyFee::class;

    public function definition(): array
    {
        $academicYear = now()->year;
        $startDate = now()->startOfYear();
        $endDate = now()->endOfYear();

        return [
            'enrollment_id' => Enrollment::factory(),
            'classroom_service_id' => ClassroomService::factory(),
            'academic_year' => $academicYear,
            'contract_number' => 'MF-' . $academicYear . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'base_amount' => $this->faker->randomFloat(2, 300, 800),
            'total_installments' => 12,
            'has_sibling_discount' => $this->faker->boolean(30),
            'sibling_discount_amount' => $this->faker->randomFloat(2, 0, 50),
            'has_punctuality_discount' => $this->faker->boolean(70),
            'punctuality_discount_amount' => $this->faker->randomFloat(2, 10, 30),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'due_day' => $this->faker->numberBetween(5, 15),
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'cancelled', 'suspended']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function withSiblingDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_sibling_discount' => true,
            'sibling_discount_amount' => $this->faker->randomFloat(2, 20, 50),
        ]);
    }

    public function withoutSiblingDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_sibling_discount' => false,
            'sibling_discount_amount' => 0,
        ]);
    }
}

