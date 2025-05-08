<?php

namespace App\Repositories;

use App\Domain\Property\Models\Unit;
use App\Repositories\Interfaces\UnitRepositoryInterface;

class UnitRepository implements UnitRepositoryInterface
{
    protected $model;

    public function __construct(Unit $model)
    {
        $this->model = $model;
    }

    public function getAllUnits($perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    public function getUnitById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function createUnit(array $unitData)
    {
        return $this->model->create($unitData);
    }

    public function updateUnit($id, array $unitData)
    {
        $unit = $this->model->findOrFail($id);
        $unit->update($unitData);
        return $unit;
    }

    public function deleteUnit($id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function getUnitsByProperty($propertyId, $perPage = 15)
    {
        return $this->model->where('property_id', $propertyId)->paginate($perPage);
    }

    public function getVacantUnits($propertyId = null, $perPage = 15)
    {
        $query = $this->model->whereDoesntHave('leases', function ($query) {
            $query->where('status', 'active');
        });

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }

        return $query->paginate($perPage);
    }
}
