<?php

namespace App\Services;

use App\Domain\Property\Models\Payment;
use App\Jobs\SendRentReminderJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RentReminderService
{
    /**
     * Schedule reminders for upcoming rent payments
     *
     * @param int $daysBeforeDue Number of days before due date to send reminder
     * @return int Number of reminders queued
     */
    public function scheduleUpcomingRentReminders(int $daysBeforeDue = 3): int
    {
        // Get the target date for which we want to send reminders
        $targetDate = Carbon::now()->addDays($daysBeforeDue)->format('Y-m-d');

        // Query for payments due on the target date with status pending
        $upcomingPayments = Payment::where('due_date', $targetDate)
            ->where('status', 'pending')
            ->with(['lease.tenant', 'lease.property'])
            ->get();

        $reminderCount = 0;

        foreach ($upcomingPayments as $payment) {
            // Make sure we have a valid lease with tenant
            if (!$payment->lease || !$payment->lease->tenant) {
                Log::warning('Cannot send reminder for payment: missing lease or tenant', [
                    'payment_id' => $payment->id,
                    'lease_id' => $payment->lease_id ?? 'Missing'
                ]);
                continue;
            }

            // Dispatch job to the queue
            SendRentReminderJob::dispatch($payment, $daysBeforeDue)
                ->onQueue('emails');

            $reminderCount++;
        }

        Log::info("Scheduled {$reminderCount} rent reminders for payments due on {$targetDate}");

        return $reminderCount;
    }

    /**
     * Send a reminder for a specific payment
     *
     * @param int $paymentId The payment ID
     * @param int $daysBeforeDue Number of days before due to indicate in reminder
     * @return bool Success status
     */
    public function sendReminderForPayment(int $paymentId, int $daysBeforeDue = 3): bool
    {
        $payment = Payment::find($paymentId);

        if (!$payment) {
            Log::error('Cannot send reminder: Payment not found', ['payment_id' => $paymentId]);
            return false;
        }

        if (!$payment->lease || !$payment->lease->tenant) {
            Log::error('Cannot send reminder: Missing lease or tenant', [
                'payment_id' => $paymentId,
                'lease_id' => $payment->lease_id ?? 'Missing'
            ]);
            return false;
        }

        // Dispatch job to queue
        SendRentReminderJob::dispatch($payment, $daysBeforeDue)
            ->onQueue('emails');

        Log::info('Manual rent reminder queued', [
            'payment_id' => $payment->id,
            'tenant_id' => $payment->lease->tenant->id,
            'due_date' => $payment->due_date
        ]);

        return true;
    }
}
