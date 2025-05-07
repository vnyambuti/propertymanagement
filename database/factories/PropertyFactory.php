<?php

namespace Database\Factories;

use App\Domain\Property\Models\Property;
use App\Domain\Property\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition()
    {
        $propertyTypes = ['apartment', 'house', 'commercial'];

        return [
            'name' => $this->faker->company() . ' ' . $this->faker->randomElement(['Apartments', 'Residences', 'Properties', 'Towers', 'Plaza']),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'county' => $this->faker->state(),
            'town' => $this->faker->city(), // Using city again as a substitute for town
            'type' => $this->faker->randomElement($propertyTypes),
            'user_id' => User::class === User::class
                ? User::factory()
                : User::factory(),
        ];
    }

    /**
     * Configure the factory to create an apartment property.
     *
     * @return static
     */
    public function apartment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'apartment',
            'name' => $this->faker->company() . ' Apartments',
        ]);
    }

    /**
     * Configure the factory to create a house property.
     *
     * @return static
     */
    public function house(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'house',
            'name' => $this->faker->streetName() . ' ' . $this->faker->randomElement(['Houses', 'Residences', 'Homes']),
        ]);
    }

    /**
     * Configure the factory to create a commercial property.
     *
     * @return static
     */
    public function commercial(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'commercial',
            'name' => $this->faker->company() . ' ' . $this->faker->randomElement(['Plaza', 'Business Center', 'Commercial Complex', 'Office Park']),
        ]);
    }

    /**
     * Configure the factory to create a property with a specific user.
     *
     * @param int $userId
     * @return static
     */
    public function forUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    /**
     * Configure the factory to create a property with specific location details.
     *
     * @param string $city
     * @param string $county
     * @param string $town
     * @return static
     */
    public function withLocation(string $city, string $county, string $town): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => $city,
            'county' => $county,
            'town' => $town,
        ]);
    }


}
