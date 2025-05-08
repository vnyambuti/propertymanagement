<?php
namespace App\Repositories\Interfaces;

interface PropertyRepositoryInterface
{
    public function getAllProperties($perPage = 15);
    public function getPropertyById($id);
    public function createProperty(array $propertyData);
    public function updateProperty($id, array $propertyData);
    public function deleteProperty($id);
    public function getPropertiesByUser($userId, $perPage = 15);
}
