<?php

namespace App\OpenApi\Schemas;

/**
 * Class PaymentSchema
 *
 * @package App\OpenApi\Schemas
 *
 * @OA\Schema(
 *     schema="Payment",
 *     title="Payment",
 *     description="Payment model representation",
 *     required={"id", "lease_id", "amount", "due_date", "status", "payment_method"},
 *     @OA\Property(
 *         property="id",
 *         title="ID",
 *         description="Payment unique identifier",
 *         type="integer",
 *         format="int64",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="lease_id",
 *         title="Lease ID",
 *         description="ID of the associated lease",
 *         type="integer",
 *         format="int64",
 *         example=2
 *     ),
 *     @OA\Property(
 *         property="amount",
 *         title="Amount",
 *         description="Payment amount",
 *         type="number",
 *         format="float",
 *         example=1200.50
 *     ),
 *     @OA\Property(
 *         property="due_date",
 *         title="Due Date",
 *         description="Date when payment is due",
 *         type="string",
 *         format="date",
 *         example="2025-06-01"
 *     ),
 *     @OA\Property(
 *         property="payment_date",
 *         title="Payment Date",
 *         description="Date when payment was made",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         example="2025-05-28"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         title="Status",
 *         description="Current payment status",
 *         type="string",
 *         enum={"pending", "completed", "overdue", "cancelled"},
 *         example="pending"
 *     ),
 *     @OA\Property(
 *         property="payment_method",
 *         title="Payment Method",
 *         description="Method used for payment",
 *         type="string",
 *         enum={"cash", "check", "credit_card", "bank_transfer", "pending"},
 *         example="bank_transfer"
 *     ),
 *     @OA\Property(
 *         property="notes",
 *         title="Notes",
 *         description="Additional payment notes",
 *         type="string",
 *         nullable=true,
 *         example="Monthly rent payment"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         title="Created At",
 *         description="Creation timestamp",
 *         type="string",
 *         format="date-time",
 *         example="2025-05-01T12:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         title="Updated At",
 *         description="Last update timestamp",
 *         type="string",
 *         format="date-time",
 *         example="2025-05-01T12:00:00Z"
 *     )
 * )
 */
class PaymentSchema
{
}
