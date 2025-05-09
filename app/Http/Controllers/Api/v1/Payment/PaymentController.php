<?php

namespace App\Http\Controllers\Api\v1\Payment;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Services\RentReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $reminderService;

    /**
     * Create a new controller instance.
     *
     * @param PaymentService $paymentService
     */
    public function __construct(PaymentService $paymentService,RentReminderService $reminderService)
    {
        $this->paymentService = $paymentService;
        $this->reminderService = $reminderService;

    }

    /**
     * Display a listing of payments.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $payments = $this->paymentService->getAllPayments($perPage);

        return response()->json($payments);
    }

    /**
     * Display the specified payment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $payment = $this->paymentService->getPaymentById($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        return response()->json($payment);
    }

    /**
     * Store a newly created payment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lease_id' => 'required|exists:leases,id',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|string|in:pending,completed,overdue,cancelled',
            'payment_method' => 'required|string|in:cash,check,credit_card,bank_transfer,pending',
            'notes' => 'nullable|string',
        ]);

        try {
            $payment = $this->paymentService->createPayment($validated);
            return response()->json($payment, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Update the specified payment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'lease_id' => 'sometimes|required|exists:leases,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'due_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|string|in:pending,completed,overdue,cancelled',
            'payment_method' => 'sometimes|required|string|in:cash,check,credit_card,bank_transfer,pending',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $payment = $this->paymentService->updatePayment($id, $validated);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        return response()->json($payment);
    }

    /**
     * Remove the specified payment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleted = $this->paymentService->deletePayment($id);

        if (!$deleted) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        return response()->json(['message' => 'Payment deleted successfully']);
    }

    /**
     * Get payments by lease ID.
     *
     * @param  Request  $request
     * @param  int  $leaseId
     * @return \Illuminate\Http\Response
     */
    public function getPaymentsByLease(Request $request, $leaseId)
    {
        $perPage = $request->input('per_page', 15);
        $payments = $this->paymentService->getPaymentsByLease($leaseId, $perPage);

        return response()->json($payments);
    }

    /**
     * Get payments by date range.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getPaymentsByDateRange(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $perPage = $request->input('per_page', 15);
        $payments = $this->paymentService->getPaymentsByDateRange(
            $validated['start_date'],
            $validated['end_date'],
            $perPage
        );

        return response()->json($payments);
    }

    /**
     * Get outstanding payments.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getOutstandingPayments(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $payments = $this->paymentService->getOutstandingPayments($perPage);

        return response()->json($payments);
    }

    /**
     * Mark payment as completed.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function markPaymentAsCompleted($id)
    {
        $payment = $this->paymentService->markPaymentAsCompleted($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }
        try {
            $this->paymentService->sendPaymentReceipt($id);
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            Log::error('Failed to queue payment receipt: ' . $e->getMessage());
        }

        return response()->json($payment);
    }

    /**
     * Generate rent invoice for a lease.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generateRentInvoice(Request $request)
    {
        $validated = $request->validate([
            'lease_id' => 'required|exists:leases,id',
        ]);

        try {
            $invoice = $this->paymentService->generateRentInvoice($validated['lease_id']);
            return response()->json($invoice, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

     /**
     * Send a payment receipt via email.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sendPaymentReceipt($id)
    {
        try {
            $result = $this->paymentService->sendPaymentReceipt($id);

            if (!$result) {
                return response()->json(['message' => 'Payment not found'], 404);
            }

            return response()->json(['message' => 'Payment receipt queued for delivery']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }


    /**
     * Send a rent reminder for a specific payment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sendRentReminder($id)
    {
        try {
            $result = $this->reminderService->sendReminderForPayment($id);

            if (!$result) {
                return response()->json(['message' => 'Payment not found or reminder could not be sent'], 404);
            }

            return response()->json(['message' => 'Rent reminder queued for delivery']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

}
