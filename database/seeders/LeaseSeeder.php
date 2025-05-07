<?php

namespace Database\Seeders;

use App\Domain\Property\Models\Lease;
use App\Domain\Property\Models\Tenant;
use App\Domain\Property\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                // Get all occupied units
                $occupiedUnits = Unit::where('status', 'occupied')->get();
                $tenants = Tenant::all();

                // Create active leases for occupied units
                foreach ($occupiedUnits as $unit) {
                    $tenant = $tenants->random();

                    // Create an active lease
                    Lease::factory()
                        ->active()
                        ->withRentAmount($unit->rent_amount)
                        ->withSecurityDeposit($unit->rent_amount * 1.5)
                        ->create([
                            'unit_id' => $unit->id,
                            'tenant_id' => $tenant->id,
                        ]);

                    // Remove this tenant from the collection to avoid duplicates
                    $tenants = $tenants->where('id', '!=', $tenant->id);

                    // Break if we run out of tenants
                    if ($tenants->isEmpty()) {
                        break;
                    }
                }

                // Create some expired leases with random units and tenants
                $availableUnits = Unit::all();

                // Create expired leases
                Lease::factory()
                    ->count(10)
                    ->expired()
                    ->sequence(fn ($sequence) => [
                        'unit_id' => $availableUnits->random()->id,
                        'tenant_id' => $tenants->isNotEmpty() ? $tenants->random()->id : Tenant::factory()->create()->id,
                    ])
                    ->create();

                // Create terminated leases
                Lease::factory()
                    ->count(5)
                    ->terminated()
                    ->sequence(fn ($sequence) => [
                        'unit_id' => $availableUnits->random()->id,
                        'tenant_id' => $tenants->isNotEmpty() ? $tenants->random()->id : Tenant::factory()->create()->id,
                    ])
                    ->create();

    }
}
