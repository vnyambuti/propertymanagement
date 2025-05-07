<?php

namespace Database\Factories;

use App\Domain\Property\Models\Property;
use App\Domain\Property\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'unit_number' => $this->faker->unique()->numerify('###'),
            'bedrooms' => $this->faker->numberBetween(0, 5),
            'bathrooms' => $this->faker->randomFloat(1, 1, 3.5),
            'square_feet' => $this->faker->numberBetween(400, 3000),
            'rent_amount' => $this->faker->randomFloat(2, 500, 5000),
            'status' => $this->faker->randomElement(['vacant', 'occupied', 'maintenance']),
        ];
    }

    /**
     * Configure the factory to create a vacant unit.
     *
     * @return static
     */
    public function vacant(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'vacant',
        ]);
    }

    /**
     * Configure the factory to create an occupied unit.
     *
     * @return static
     */
    public function occupied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'occupied',
        ]);
    }

    /**
     * Configure the factory to create a unit under maintenance.
     *
     * @return static
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
        ]);
    }

    /**
     * Configure the factory to create a studio unit.
     *
     * @return static
     */
    public function studio(): static
    {
        return $this->state(fn (array $attributes) => [
            'bedrooms' => 0,
            'bathrooms' => 1,
            'square_feet' => $this->faker->numberBetween(350, 650),
            'rent_amount' => $this->faker->randomFloat(2, 500, 1500),
        ]);
    }

    /**
     * Configure the factory to create a luxury unit.
     *
     * @return static
     */
    public function luxury(): static
    {
        return $this->state(fn (array $attributes) => [
            'bedrooms' => $this->faker->numberBetween(2, 5),
            'bathrooms' => $this->faker->randomFloat(1, 2, 4.5),
            'square_feet' => $this->faker->numberBetween(1200, 4000),
            'rent_amount' => $this->faker->randomFloat(2, 3000, 10000),
        ]);
    }

    /**
     * Configure the factory to create a unit with a specific number of bedrooms.
     *
     * @param int $bedrooms
     * @return static
     */
    public function withBedrooms(int $bedrooms): static
    {
        return $this->state(fn (array $attributes) => [
            'bedrooms' => $bedrooms,
        ]);
    }

    /**
     * Configure the factory to create a unit with a specific number of bathrooms.
     *
     * @param float $bathrooms
     * @return static
     */
    public function withBathrooms(float $bathrooms): static
    {
        return $this->state(fn (array $attributes) => [
            'bathrooms' => $bathrooms,
        ]);
    }

    /**
     * Configure the factory to create a unit with a specific square footage.
     *
     * @param int $squareFeet
     * @return static
     */
    public function withSquareFeet(int $squareFeet): static
    {
        return $this->state(fn (array $attributes) => [
            'square_feet' => $squareFeet,
        ]);
    }

    /**
     * Configure the factory to create a unit with a specific rent amount.
     *
     * @param float $rentAmount
     * @return static
     */
    public function withRentAmount(float $rentAmount): static
    {
        return $this->state(fn (array $attributes) => [
            'rent_amount' => $rentAmount,
        ]);
    }

    /**
     * Configure the factory to create a unit with a specific unit number.
     *
     * @param string $unitNumber
     * @return static
     */
    public function withUnitNumber(string $unitNumber): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_number' => $unitNumber,
        ]);
    }
    }

