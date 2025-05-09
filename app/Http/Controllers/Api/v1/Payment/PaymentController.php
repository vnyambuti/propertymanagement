<?php

namespace App\Http\Controllers\Api\v1\Payment;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Services\RentReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Payments",
 *     description="API Endpoints for Payment Management"
 * )
 */
class PaymentController extends Controller
{
    protected $paymentService;
    protected $reminderService;

    /**
     * Create a new controller instance.
     *
     * @param PaymentService $paymentService
     * @param RentReminderService $reminderService
     */
    public function __construct(PaymentService $paymentService,RentReminderService $reminderService)
    {
        $this->paymentService = $paymentService;
        $this->reminderService = $reminderService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/payments",
     *     summary="Get a list of payments",
     *     description="Returns a paginated list of all payments",
     *     operationId="getPaymentsList",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=15
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Payment")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     *
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
     * @OA\Get(
     *     path="/api/v1/payments/{id}",
     *     summary="Get payment details",
     *     description="Returns details for a specific payment",
     *     operationId="getPaymentById",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Payment ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Payment")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment not found")
     *         )
     *     )
     * )
     *
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
     * @OA\Post(
     *     path="/api/v1/payments",
     *     summary="Create a new payment",
     *     description="Creates a new payment record",
     *     operationId="storePayment",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"lease_id", "amount", "due_date", "status", "payment_method"},
     *             @OA\Property(property="lease_id", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", format="float", example=1200.50),
     *             @OA\Property(property="due_date", type="string", format="date", example="2025-06-01"),
     *             @OA\Property(property="status", type="string", enum={"pending", "completed", "overdue", "cancelled"}, example="pending"),
     *             @OA\Property(property="payment_method", type="string", enum={"cash", "check", "credit_card", "bank_transfer", "pending"}, example="bank_transfer"),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Monthly rent payment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Payment")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The lease_id field is required.")
     *         )
     *     )
     * )
     *
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
     * @OA\Put(
     *     path="/api/v1/payments/{id}",
     *     summary="Update payment details",
     *     description="Updates an existing payment record",
     *     operationId="updatePayment",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Payment ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="lease_id", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", format="float", example=1200.50),
     *             @OA\Property(property="due_date", type="string", format="date", example="2025-06-01"),
     *             @OA\Property(property="status", type="string", enum={"pending", "completed", "overdue", "cancelled"}, example="completed"),
     *             @OA\Property(property="payment_method", type="string", enum={"cash", "check", "credit_card", "bank_transfer", "pending"}, example="credit_card"),
     *             @OA\Property(property="payment_date", type="string", format="date", nullable=true, example="2025-05-28"),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Payment received")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Payment")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The amount must be a number.")
     *         )
     *     )
     * )
     *
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
     * @OA\Delete(
     *     path="/api/v1/payments/{id}",
     *     summary="Delete a payment",
     *     description="Deletes a payment record",
     *     operationId="deletePayment",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Payment ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment not found")
     *         )
     *     )
     * )
     *
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
     * @OA\Get(
     *     path="/api/v1/leases/{leaseId}/payments",
     *     summary="Get payments by lease ID",
     *     description="Returns a paginated list of payments for a specific lease",
     *     operationId="getPaymentsByLease",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="leaseId",
     *         in="path",
     *         description="Lease ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=15
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Payment")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     *
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
     * @OA\Get(
     *     path="/api/v1/payments/date-range",
     *     summary="Get payments by date range",
     *     description="Returns a paginated list of payments within a specified date range",
     *     operationId="getPaymentsByDateRange",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date (YYYY-MM-DD)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date (YYYY-MM-DD)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=15
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Payment")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid date range",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The end date must be a date after or equal to start date.")
     *         )
     *     )
     * )
     *
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
     * @OA\Get(
     *     path="/api/v1/payments/outstanding",
     *     summary="Get outstanding payments",
     *     description="Returns a paginated list of outstanding payments",
     *     operationId="getOutstandingPayments",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=15
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Payment")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     *
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
     * @OA\Post(
     *     path="/api/v1/payments/{id}/complete",
     *     summary="Mark payment as completed",
     *     description="Updates a payment status to completed",
     *     operationId="markPaymentAsCompleted",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Payment ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment marked as completed",
     *         @OA\JsonContent(ref="#/components/schemas/Payment")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment not found")
     *         )
     *     )
     * )
     *
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
     * @OA\Post(
     *     path="/api/v1/payments/generate-invoice",
     *     summary="Generate rent invoice",
     *     description="Generates a rent invoice for a specific lease",
     *     operationId="generateRentInvoice",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"lease_id"},
     *             @OA\Property(property="lease_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Invoice generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=123),
     *             @OA\Property(property="lease_id", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", format="float", example=1200.50),
     *             @OA\Property(property="due_date", type="string", format="date", example="2025-06-01"),
     *             @OA\Property(property="status", type="string", example="pending")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error generating invoice",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     *
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
     * @OA\Post(
     *     path="/api/v1/payments/{id}/send-receipt",
     *     summary="Send payment receipt",
     *     description="Sends a payment receipt via email",
     *     operationId="sendPaymentReceipt",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Payment ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Receipt queued for delivery",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment receipt queued for delivery")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error sending receipt",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     *
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
     * @OA\Post(
     *     path="/api/v1/payments/{id}/send-reminder",
     *     summary="Send rent reminder",
     *     description="Sends a rent reminder for a specific payment",
     *     operationId="sendRentReminder",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Payment ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reminder queued for delivery",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Rent reminder queued for delivery")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found or reminder could not be sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment not found or reminder could not be sent")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error sending reminder",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     *
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
