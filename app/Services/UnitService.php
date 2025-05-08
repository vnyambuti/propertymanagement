<?php
namespace App\Services;

use App\Repositories\Interfaces\UnitRepositoryInterface;

class UnitService
{
    protected $unitRepository;

    public function __construct(UnitRepositoryInterface $unitRepository)
    {
        $this->unitRepository = $unitRepository;
    }

    public function getAllUnits($perPage = 15)
    {
        return $this->unitRepository->getAllUnits($perPage);
    }

    public function getUnitById($id)
    {
        return $this->unitRepository->getUnitById($id);
    }

    public function createUnit(array $unitData)
    {
        return $this->unitRepository->createUnit($unitData);
    }

    public function updateUnit($id, array $unitData)
    {
        return $this->unitRepository->updateUnit($id, $unitData);
    }

    public function deleteUnit($id)
    {
        return $this->unitRepository->deleteUnit($id);
    }

    public function getUnitsByProperty($propertyId, $perPage = 15)
    {
        return $this->unitRepository->getUnitsByProperty($propertyId, $perPage);
    }

    public function getVacantUnits($propertyId = null, $perPage = 15)
    {
        return $this->unitRepository->getVacantUnits($propertyId, $perPage);
    }
}
