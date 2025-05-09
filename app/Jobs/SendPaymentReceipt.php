<?php

namespace App\Jobs;

use App\Mail\PaymentReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payment;
    protected $tenant;
    protected $property;
    protected $unit;
    protected $email;

    /**
     * Create a new job instance.
     */
    public function __construct($payment, $tenant, $property, $unit, $email)
    {
        $this->payment = $payment;
        $this->tenant = $tenant;
        $this->property = $property;
        $this->unit = $unit;
        $this->email = $email;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // If using log driver for development, log the email details
        if (config('mail.default') === 'log') {
            Log::info('Sending payment receipt email', [
                'payment_id' => $this->payment->id,
                'tenant_id' => $this->tenant->id,
                'tenant_email' => $this->email,
                'amount' => $this->payment->amount,
                'payment_date' => $this->payment->payment_date,
            ]);
        }

        // Send the email
        Mail::to($this->email)
            ->send(new PaymentReceipt(
                $this->payment,
                $this->tenant,
                $this->property,
                $this->unit
            ));
    }
}
