<?php

namespace Database\Seeders;

use App\Domain\Property\Models\Property;
use App\Domain\Property\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          // Get all properties
          $properties = Property::all();

          foreach ($properties as $property) {
              // For apartment properties, create multiple units
              if ($property->type === 'apartment') {
                  // Create some vacant units
                  Unit::factory()
                      ->count(rand(1, 3))
                      ->vacant()
                      ->for($property)
                      ->create();

                  // Create some occupied units
                  Unit::factory()
                      ->count(rand(2, 5))
                      ->occupied()
                      ->for($property)
                      ->create();

                  // Create some maintenance units
                  Unit::factory()
                      ->count(rand(0, 2))
                      ->maintenance()
                      ->for($property)
                      ->create();

                  // Create a studio unit
                  Unit::factory()
                      ->studio()
                      ->for($property)
                      ->create();

                  // Create a luxury unit
                  Unit::factory()
                      ->luxury()
                      ->for($property)
                      ->create();
              }
              // For houses, create a single unit
              elseif ($property->type === 'house') {
                  Unit::factory()
                      ->withBedrooms(rand(2, 5))
                      ->withBathrooms(rand(1, 3) + (rand(0, 1) * 0.5))
                      ->for($property)
                      ->create([
                          'unit_number' => 'Main',
                          'status' => rand(0, 1) ? 'vacant' : 'occupied',
                      ]);
              }
              // For commercial, create varying units
              elseif ($property->type === 'commercial') {
                  Unit::factory()
                      ->count(rand(3, 8))
                      ->sequence(
                          ['status' => 'vacant'],
                          ['status' => 'occupied'],
                          ['status' => 'maintenance'],
                      )
                      ->for($property)
                      ->create();
              }
          }
    }
}
