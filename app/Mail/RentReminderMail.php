<?php

namespace App\Mail;

use App\Domain\Property\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $daysBeforeDue;

    /**
     * Create a new message instance.
     *
     * @param Payment $payment
     * @param int $daysBeforeDue
     * @return void
     */
    public function __construct(Payment $payment, int $daysBeforeDue)
    {
        $this->payment = $payment;
        $this->daysBeforeDue = $daysBeforeDue;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $property = $this->payment->lease->property;
        $subject = "Rent Payment Reminder - Due in {$this->daysBeforeDue} days";

        return $this->subject($subject)
            ->markdown('emails.rent-reminder', [
                'payment' => $this->payment,
                'tenant' => $this->payment->lease->tenant,
                'property' => $property,
                'daysBeforeDue' => $this->daysBeforeDue
            ]);
    }
}
