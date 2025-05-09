<?php

namespace App\Domain\Property\Models;

use App\Domain\Lease\Models\Lease;
use App\Domain\Property\Models\Lease as ModelsLease;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /**
 * @OA\Schema(
 *     schema="Payment",
 *     required={"id", "lease_id", "amount", "due_date", "status", "payment_method"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="lease_id", type="integer", format="int64", example=2),
 *     @OA\Property(property="amount", type="number", format="float", example=1200.50),
 *     @OA\Property(property="due_date", type="string", format="date", example="2025-06-01"),
 *     @OA\Property(property="payment_date", type="string", format="date", nullable=true, example="2025-05-28"),
 *     @OA\Property(property="status", type="string", enum={"pending", "completed", "overdue", "cancelled"}, example="pending"),
 *     @OA\Property(property="payment_method", type="string", enum={"cash", "check", "credit_card", "bank_transfer", "pending"}, example="bank_transfer"),
 *     @OA\Property(property="notes", type="string", nullable=true, example="Monthly rent payment"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-01T12:00:00Z")
 * )
 */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lease_id',
        'amount',
        'payment_date',
        'payment_method',
        'due_date',
        'status',
        'transaction_id',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_date' => 'date',
    ];

    /**
     * Get the lease that the payment is for.
     */
    public function lease()
    {
        return $this->belongsTo(ModelsLease::class);
    }

    /**
     * Scope a query to only include completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if payment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    protected static function newFactory()
    {
        return \Database\Factories\LeaseFactory::new();
    }

}
