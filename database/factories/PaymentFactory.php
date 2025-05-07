<?php

namespace Database\Factories;

use App\Domain\Property\Models\Lease;
use App\Domain\Property\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        $paymentMethods = ['cash','credit_card', 'bank_transfer', 'm-pesa'];
        $paymentStatuses = ['pending', 'paid', 'overdue'];

        $lease = Lease::inRandomOrder()->first();
        $dueDate = $this->faker->dateTimeBetween('-3 months', '+1 month');
        $paymentDate = clone $dueDate;

        // 70% chance payment was made on time
        $paymentStatus = $this->faker->randomElement($paymentStatuses);

        if ($paymentStatus === 'paid') {
            // If paid, payment date is before or on due date
            $paymentDate = $this->faker->dateTimeBetween(
                $paymentDate->modify('-10 days'),
                $dueDate
            );
        } elseif ($paymentStatus === 'overdue') {
            // If overdue, due date is in the past
            $dueDate = $this->faker->dateTimeBetween('-3 months', '-1 day');
            $paymentDate = null; // No payment made yet
        } else {
            // If pending, payment hasn't been made yet
            $paymentDate = null;
        }

        $amount = $lease ? $lease->monthly_rent : $this->faker->numberBetween(800, 3500);

        return [
            'lease_id' => $lease ? $lease->id : Lease::factory(),
            'amount' => $amount,
            'payment_date' => $paymentDate,
            'due_date' => $dueDate,
            'payment_method' => $paymentStatus === 'paid' ? $this->faker->randomElement($paymentMethods) : null,
            'status' => $paymentStatus,
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
        ];
    }

    public function paid()
    {
        return $this->state(function (array $attributes) {
            $dueDate = $this->faker->dateTimeBetween('-2 months', '-1 day');
            $paymentDate = $this->faker->dateTimeBetween(
                (clone $dueDate)->modify('-5 days'),
                $dueDate
            );

            return [
                'payment_date' => $paymentDate,
                'due_date' => $dueDate,
                'payment_status' => 'paid',
                'payment_method' => $this->faker->randomElement(['cash', 'credit_card', 'bank_transfer', 'm-pesa']),
            ];
        });
    }

    public function overdue()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_date' => null,
                'due_date' => $this->faker->dateTimeBetween('-2 months', '-3 days'),
                'payment_status' => 'overdue',
                'payment_method' => null,
            ];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_date' => null,
                'due_date' => $this->faker->dateTimeBetween('now', '+15 days'),
                'payment_status' => 'pending',
                'payment_method' => null,
            ];
        });
    }
}
