<?php

namespace Database\Factories;

use App\Models\MonthlyFeePayment;
use App\Models\MonthlyFeeInstallment;
use App\Models\Guardian;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonthlyFeePaymentFactory extends Factory
{
    protected $model = MonthlyFeePayment::class;

    public function definition(): array
    {
        $originalAmount = $this->faker->randomFloat(2, 300, 800);
        $discountApplied = $this->faker->randomFloat(2, 0, 50);
        $interestApplied = $this->faker->randomFloat(2, 0, 30);
        $fineApplied = $this->faker->randomFloat(2, 0, 50);
        
        $amount = $originalAmount - $discountApplied + $interestApplied + $fineApplied;

        return [
            'monthly_fee_installment_id' => MonthlyFeeInstallment::factory(),
            'paid_by_guardian_id' => Guardian::factory(),
            'payment_number' => 'PAY-' . now()->year . '-' . str_pad($this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'amount' => $amount,
            'original_installment_amount' => $originalAmount,
            'discount_applied' => $discountApplied,
            'interest_applied' => $interestApplied,
            'fine_applied' => $fineApplied,
            'method' => $this->faker->randomElement(['pix', 'credit_card', 'debit_card', 'cash', 'bank_transfer']),
            'payment_date' => now(),
            'confirmation_date' => now(),
            'status' => $this->faker->randomElement(['confirmed', 'confirmed', 'confirmed', 'pending']),
            'reference' => $this->faker->optional()->uuid(),
            'transaction_id' => $this->faker->optional()->uuid(),
            'enrollment_invoice_id' => null,
            'enrollment_payment_id' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'confirmation_date' => null,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'confirmation_date' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
        ]);
    }
}

