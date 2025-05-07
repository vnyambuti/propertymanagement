<?php

namespace Database\Seeders;


use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

         // Call your other seeders
         $this->call([
            UserSeeder::class,
            TenantSeeder::class,
            PropertySeeder::class,
            UnitSeeder::class,
            LeaseSeeder::class,
            PaymentSeeder::class,


        ]);
    }
}
