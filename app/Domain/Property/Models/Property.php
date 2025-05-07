<?php

namespace App\Domain\Property\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domain\Property\Models\Unit;
use App\Domain\Property\Models\User as ModelsUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'town',
        'county',
        'type',
        'user_id',
    ];

    /**
     * Get the user that owns the property.
     */
    public function user()
    {
        return $this->belongsTo(ModelsUser::class);
    }

    /**
     * Get the units for the property.
     */
    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    protected static function newFactory()
    {
        return \Database\Factories\PropertyFactory::new();
    }

}
