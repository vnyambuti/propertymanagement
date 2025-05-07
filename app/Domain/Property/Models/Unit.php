<?php

namespace App\Domain\Property\Models;

use App\Domain\Lease\Models\Lease;
use App\Domain\Property\Models\Lease as ModelsLease;
use App\infrastructure\Database\Seeders\LeaseSeeder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'property_id',
        'unit_number',
        'bedrooms',
        'bathrooms',
        'square_feet',
        'rent_amount',
        'status',
    ];

    /**
     * Get the property that owns the unit.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the leases for the unit.
     */
    public function leases()
    {
        return $this->hasMany(ModelsLease::class);
    }

    /**
     * Get active lease for the unit.
     */
    public function activeLease()
    {
        return $this->hasOne(ModelsLease::class)->where('status', 'active');
    }

    protected static function newFactory()
    {
        return \Database\Factories\UnitFactory::new();
    }


}
