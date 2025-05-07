<?php

namespace Database\Seeders;

use App\Domain\Property\Models\Property;
use App\Domain\Property\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          // Get all users
          $users = User::all();

          // Create some apartment properties
          Property::factory()
              ->count(5)
              ->apartment()
              ->sequence(fn ($sequence) => ['user_id' => $users->random()->id])
              ->create();

          // Create some house properties
          Property::factory()
              ->count(3)
              ->house()
              ->sequence(fn ($sequence) => ['user_id' => $users->random()->id])
              ->create();

          // Create some commercial properties
          Property::factory()
              ->count(2)
              ->commercial()
              ->sequence(fn ($sequence) => ['user_id' => $users->random()->id])
              ->create();

          // Create a property with specific location
          Property::factory()
              ->withLocation('New York', 'New York', 'Manhattan')
              ->apartment()
              ->forUser($users->first()->id)
              ->create();
    }
}
