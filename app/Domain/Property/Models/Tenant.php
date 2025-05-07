<?php

namespace App\Domain\Property\Models;

use App\Domain\Lease\Models\Lease;
use App\Domain\Property\Models\Lease as ModelsLease;
use App\infrastructure\Database\Factories\Domain\Property\Models\TenantFactory as ModelsTenantFactory;
use App\infrastructure\Database\Factories\TenantFactory;
use App\infrastructure\Database\Seeders\LeaseSeeder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
    ];

    /**
     * Get the leases for the tenant.
     */
    public function leases()
    {
        return $this->hasMany(ModelsLease::class);
    }

    /**
     * Get active lease for the tenant.
     */
    public function activeLease()
    {
        return $this->hasOne(ModelsLease::class)->where('status', 'active');
    }



    /**
     * Get the full name attribute.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    protected static function newFactory()
{
    return \Database\Factories\TenantFactory::new();
}


}
