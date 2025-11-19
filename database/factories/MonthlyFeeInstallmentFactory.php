<?php

namespace Database\Factories;

use App\Models\MonthlyFeeInstallment;
use App\Models\MonthlyFee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonthlyFeeInstallmentFactory extends Factory
{
    protected $model = MonthlyFeeInstallment::class;

    public function definition(): array
    {
        $baseAmount = $this->faker->randomFloat(2, 300, 800);
        $siblingDiscount = $this->faker->randomFloat(2, 0, 50);
        $punctualityDiscount = $this->faker->randomFloat(2, 0, 30);
        $interestAmount = 0;
        $fineAmount = 0;
        
        $finalAmount = $baseAmount - $siblingDiscount - $punctualityDiscount + $interestAmount + $fineAmount;

        $installmentNumber = $this->faker->numberBetween(1, 12);
        $dueDate = Carbon::create(now()->year, $installmentNumber, 10);

        return [
            'monthly_fee_id' => MonthlyFee::factory(),
            'reference_month' => $dueDate->format('Y-m'),
            'installment_number' => $installmentNumber,
            'base_amount' => $baseAmount,
            'sibling_discount' => $siblingDiscount,
            'punctuality_discount' => $punctualityDiscount,
            'other_discounts' => 0,
            'interest_amount' => $interestAmount,
            'fine_amount' => $fineAmount,
            'final_amount' => $finalAmount,
            'due_date' => $dueDate,
            'status' => $this->faker->randomElement(['pending', 'paid', 'overdue']),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_date' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_date' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => now()->subDays($this->faker->numberBetween(1, 30)),
            'interest_amount' => $this->faker->randomFloat(2, 5, 30),
            'fine_amount' => $this->faker->randomFloat(2, 10, 50),
        ]);
    }
}

