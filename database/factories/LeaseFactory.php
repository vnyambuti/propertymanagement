<?php

namespace Database\Factories;

use App\Domain\Property\Models\Lease;
use App\Domain\Property\Models\Property;
use App\Domain\Property\Models\Unit;
use App\Domain\Property\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class LeaseFactory extends Factory
{
    protected $model = Lease::class;


        public function definition(): array
        {
            $startDate = $this->faker->dateTimeBetween('-1 year', '+1 month');
            $endDate = Carbon::instance($startDate)->addMonths($this->faker->numberBetween(6, 24));

            return [
                'unit_id' => Unit::factory(),
                'tenant_id' => Tenant::factory(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'rent_amount' => $this->faker->randomFloat(2, 500, 5000),
                'security_deposit' => $this->faker->randomFloat(2, 500, 3000),
                'status' => $this->faker->randomElement(['active', 'terminated', 'expired']),
                'notes' => $this->faker->optional(0.7)->paragraph(),
            ];
        }

        /**
         * Configure the factory to create an active lease.
         *
         * @return static
         */
        public function active(): static
        {
            return $this->state(fn (array $attributes) => [
                'status' => 'active',
            ]);
        }

        /**
         * Configure the factory to create a terminated lease.
         *
         * @return static
         */
        public function terminated(): static
        {
            return $this->state(fn (array $attributes) => [
                'status' => 'terminated',
            ]);
        }

        /**
         * Configure the factory to create an expired lease.
         *
         * @return static
         */
        public function expired(): static
        {
            return $this->state(fn (array $attributes) => [
                'status' => 'expired',
                'end_date' => Carbon::now()->subDays($this->faker->numberBetween(1, 90)),
            ]);
        }

        /**
         * Configure the factory to create a lease with a specific rent amount.
         *
         * @param float $amount
         * @return static
         */
        public function withRentAmount(float $amount): static
        {
            return $this->state(fn (array $attributes) => [
                'rent_amount' => $amount,
            ]);
        }

        /**
         * Configure the factory to create a lease with a specific security deposit.
         *
         * @param float $amount
         * @return static
         */
        public function withSecurityDeposit(float $amount): static
        {
            return $this->state(fn (array $attributes) => [
                'security_deposit' => $amount,
            ]);
        }

        /**
         * Configure the factory to create a lease with a specific duration in months.
         *
         * @param int $months
         * @return static
         */
        public function withDuration(int $months): static
        {
            return $this->state(function (array $attributes) use ($months) {
                $startDate = $attributes['start_date'] instanceof Carbon
                    ? $attributes['start_date']
                    : Carbon::parse($attributes['start_date']);

                return [
                    'end_date' => $startDate->copy()->addMonths($months),
                ];
            });
        }

    }

