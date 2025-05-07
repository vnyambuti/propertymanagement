<?php

namespace Database\Seeders;

use App\infrastructure\Database\Seeders\LeaseSeeder;
use App\infrastructure\Database\Seeders\PaymentSeeder;
use App\infrastructure\Database\Seeders\PropertySeeder;
use App\infrastructure\Database\Seeders\TenantSeeder;
use App\infrastructure\Database\Seeders\UnitSeeder;
use App\infrastructure\Database\Seeders\UserSeeder;
use Illuminate\Database\Seeder;

class InfrastructureDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Call your other seeders
        $this->call([
            TenantSeeder::class,
            PropertySeeder::class,
            UnitSeeder::class,
            LeaseSeeder::class,
            PaymentSeeder::class,
            UserSeeder::class

        ]);
    }
}
