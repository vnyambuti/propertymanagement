<?php

namespace Database\Seeders;

use App\Domain\Property\Models\Lease;
use App\Domain\Property\Models\Payment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          // Get all active leases
          $activeLeases = Lease::where('status', 'active')->get();

          // For each active lease, create payment history
          foreach ($activeLeases as $lease) {
              $startDate = Carbon::parse($lease->start_date);
              $endDate = Carbon::parse($lease->end_date);
              $currentDate = Carbon::now();

              // Calculate how many months have passed since lease started
              $monthsPassed = $startDate->diffInMonths($currentDate);

              // Generate payments for past months
              for ($i = 0; $i < $monthsPassed; $i++) {
                  $dueDate = $startDate->copy()->addMonths($i);

                  // 80% chance payment was made on time
                  if (rand(1, 100) <= 80) {
                      // Payment made between 5 days before and on due date
                      $paymentDate = $dueDate->copy()->subDays(rand(0, 5));

                      Payment::create([
                          'lease_id' => $lease->id,
                          'amount' => $lease->rent_amount,
                          'payment_date' => $paymentDate,
                          'due_date' => $dueDate,
                          'payment_method' => ['cash', 'credit_card', 'bank_transfer', 'online_payment'][rand(0, 3)],
                          'status' => 'paid',
                          'notes' => rand(0, 10) > 7 ? 'Payment received on time' : null,
                      ]);
                  }
                  // 10% chance payment was late
                  elseif (rand(1, 100) <= 90) {
                      // Payment made between 1 and 10 days after due date
                      $paymentDate = $dueDate->copy()->addDays(rand(1, 10));

                      Payment::create([
                          'lease_id' => $lease->id,
                          'amount' => $lease->rent_amount,
                          'payment_date' => $paymentDate,
                          'due_date' => $dueDate,
                          'payment_method' => ['cash', 'credit_card', 'bank_transfer', 'online_payment'][rand(0, 3)],
                          'status' => 'paid',
                          'notes' => 'Payment received late',
                      ]);
                  }
                  // 10% chance payment is still overdue
                  else {
                      Payment::create([
                          'lease_id' => $lease->id,
                          'amount' => $lease->rent_amount,
                          'payment_date' => null,
                          'due_date' => $dueDate,
                          'payment_method' => null,
                          'status' => 'overdue',
                          'notes' => 'Payment overdue - tenant contacted',
                      ]);
                  }
              }

              // Create pending payment for current month if it's not created yet
              $currentMonthDueDate = $startDate->copy()->addMonths($monthsPassed);

              if ($currentMonthDueDate->month == Carbon::now()->month) {
                  // If due date has passed but is still this month
                  if ($currentMonthDueDate->isPast()) {
                      // 70% paid, 30% overdue
                      if (rand(1, 100) <= 70) {
                          $paymentDate = $currentMonthDueDate->copy()->subDays(rand(0, 3));

                          Payment::create([
                              'lease_id' => $lease->id,
                              'amount' => $lease->rent_amount,
                              'payment_date' => $paymentDate,
                              'due_date' => $currentMonthDueDate,
                              'payment_method' => ['cash', 'credit_card', 'bank_transfer', 'online_payment'][rand(0, 3)],
                              'status' => 'paid',
                              'notes' => null,
                          ]);
                      } else {
                          Payment::create([
                              'lease_id' => $lease->id,
                              'amount' => $lease->rent_amount,
                              'payment_date' => null,
                              'due_date' => $currentMonthDueDate,
                              'payment_method' => null,
                              'status' => 'overdue',
                              'notes' => 'Payment overdue',
                          ]);
                      }
                  } else {
                      // Due date is in the future this month
                      Payment::create([
                          'lease_id' => $lease->id,
                          'amount' => $lease->rent_amount,
                          'payment_date' => null,
                          'due_date' => $currentMonthDueDate,
                          'payment_method' => null,
                          'status' => 'pending',
                          'notes' => null,
                      ]);
                  }
              }

              // Create pending payment for next month
              $nextMonthDueDate = $startDate->copy()->addMonths($monthsPassed + 1);

              if ($nextMonthDueDate->lt($endDate)) {
                  Payment::create([
                      'lease_id' => $lease->id,
                      'amount' => $lease->rent_amount,
                      'payment_date' => null,
                      'due_date' => $nextMonthDueDate,
                      'payment_method' => null,
                      'status' => 'pending',
                      'notes' => null,
                  ]);
              }
          }
    }
}
