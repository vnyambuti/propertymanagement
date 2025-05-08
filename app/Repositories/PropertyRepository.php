<?php
namespace App\Repositories;

use App\Domain\Property\Models\Property;
use App\Repositories\Interfaces\PropertyRepositoryInterface;

class PropertyRepository implements PropertyRepositoryInterface
{
    protected $model;

    public function __construct(Property $model)
    {
        $this->model = $model;
    }

    public function getAllProperties($perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    public function getPropertyById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function createProperty(array $propertyData)
    {
        return $this->model->create($propertyData);
    }

    public function updateProperty($id, array $propertyData)
    {
        $property = $this->model->findOrFail($id);
        $property->update($propertyData);
        return $property;
    }

    public function deleteProperty($id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function getPropertiesByUser($userId, $perPage = 15)
    {
        return $this->model->where('user_id', $userId)->paginate($perPage);
    }
}
