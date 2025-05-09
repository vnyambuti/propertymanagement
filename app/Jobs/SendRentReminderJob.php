<?php

namespace App\Jobs;

use App\Domain\Property\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Mail\RentReminderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendRentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payment;
    protected $daysBeforeDue;

    /**
     * Create a new job instance.
     *
     * @param Payment $payment
     * @param int $daysBeforeDue
     * @return void
     */
    public function __construct(Payment $payment, int $daysBeforeDue = 3)
    {
        $this->payment = $payment;
        $this->daysBeforeDue = $daysBeforeDue;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Get tenant email from the relationship
            $tenant = $this->payment->lease->tenant;

            if (!$tenant || !$tenant->email) {
                Log::error('Unable to send rent reminder: No tenant email found', [
                    'payment_id' => $this->payment->id,
                    'lease_id' => $this->payment->lease_id
                ]);
                return;
            }

            // Send the reminder email
            Mail::to($tenant->email)->send(new RentReminderMail($this->payment, $this->daysBeforeDue));

            Log::info('Rent reminder sent successfully', [
                'payment_id' => $this->payment->id,
                'tenant_id' => $tenant->id,
                'email' => $tenant->email,
                'due_date' => $this->payment->due_date,
                'amount' => $this->payment->amount
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send rent reminder: ' . $e->getMessage(), [
                'payment_id' => $this->payment->id,
                'exception' => $e->getMessage()
            ]);

            // Determine if we should retry the job
            if ($this->attempts() < 3) {
                // Release the job back to the queue with a delay
                $this->release(300); // 5 minutes delay
            }
        }
    }
}
