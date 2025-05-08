<?php
namespace App\Services;

use App\Repositories\Interfaces\PropertyRepositoryInterface;

class PropertyService
{
    protected $propertyRepository;

    public function __construct(PropertyRepositoryInterface $propertyRepository)
    {
        $this->propertyRepository = $propertyRepository;
    }

    public function getAllProperties($perPage = 15)
    {
        return $this->propertyRepository->getAllProperties($perPage);
    }

    public function getPropertyById($id)
    {
        return $this->propertyRepository->getPropertyById($id);
    }

    public function createProperty(array $propertyData)
    {
        return $this->propertyRepository->createProperty($propertyData);
    }

    public function updateProperty($id, array $propertyData)
    {
        return $this->propertyRepository->updateProperty($id, $propertyData);
    }

    public function deleteProperty($id)
    {
        return $this->propertyRepository->deleteProperty($id);
    }

    public function getPropertiesByUser($userId, $perPage = 15)
    {
        return $this->propertyRepository->getPropertiesByUser($userId, $perPage);
    }
}
