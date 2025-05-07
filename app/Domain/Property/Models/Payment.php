<?php

namespace App\Domain\Property\Models;

use App\Domain\Lease\Models\Lease;
use App\Domain\Property\Models\Lease as ModelsLease;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
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
