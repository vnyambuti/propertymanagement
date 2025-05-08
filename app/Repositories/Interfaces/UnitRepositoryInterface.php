<?php
namespace App\Repositories\Interfaces;

interface UnitRepositoryInterface
{
    public function getAllUnits($perPage = 15);
    public function getUnitById($id);
    public function createUnit(array $unitData);
    public function updateUnit($id, array $unitData);
    public function deleteUnit($id);
    public function getUnitsByProperty($propertyId, $perPage = 15);
    public function getVacantUnits($propertyId = null, $perPage = 15);
}
