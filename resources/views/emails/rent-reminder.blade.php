@component('mail::message')
# Rent Payment Reminder

Dear {{ $tenant->name }},

This is a friendly reminder that your rent payment of **${{ number_format($payment->amount, 2) }}** for the property at **{{ $property->address }}** is due in **{{ $daysBeforeDue }}** day(s) on **{{ \Carbon\Carbon::parse($payment->due_date)->format('F j, Y') }}**.

## Payment Details:
- **Amount Due**: ${{ number_format($payment->amount, 2) }}
- **Due Date**: {{ \Carbon\Carbon::parse($payment->due_date)->format('F j, Y') }}
- **Property**: {{ $property->address }}
- **Lease Reference**: #{{ $payment->lease_id }}

@component('mail::button', ['url' => config('app.url').'/payments/'.$payment->id])
Pay Online
@endcomponent

Please ensure your payment is made on time to avoid any late fees. If you have already made this payment, please disregard this message.

If you have any questions or concerns regarding your payment, please contact us.

Thank you,<br>
{{ config('app.name') }}

@component('mail::subcopy')
  This is an automated reminder. Please do not reply to this email.
@endcomponent
@endcomponent
