<?php

namespace App\Domain\Property\Models;

use App\Domain\Payment\Models\Payment;
use App\Domain\Property\Models\Payment as ModelsPayment;
use App\Domain\Property\Models\Tenant as ModelsTenant;
use App\Domain\Property\Models\Unit;
use App\Domain\Tenant\Models\Tenant;
use App\infrastructure\Database\Factories\LeaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unit_id',
        'tenant_id',
        'start_date',
        'end_date',
        'rent_amount',
        'security_deposit',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the unit that the lease is for.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the tenant that the lease is for.
     */
    public function tenant()
    {
        return $this->belongsTo(ModelsTenant::class);
    }

    /**
     * Get the payments for the lease.
     */
    public function payments()
    {
        return $this->hasMany(ModelsPayment::class);
    }

    /**
     * Scope a query to only include active leases.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if lease is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Calculate the total paid amount for this lease.
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    /**
     * Calculate lease duration in months.
     */
    public function getDurationMonthsAttribute()
    {
        return $this->start_date->diffInMonths($this->end_date);
    }
    protected static function newFactory()
    {
        return \Database\Factories\LeaseFactory::new();
    }


}
