<?php
namespace App\Repositories;

use App\Domain\Property\Models\Tenant;
use App\Repositories\Interfaces\TenantRepositoryInterface;

class TenantRepository implements TenantRepositoryInterface
{
    protected $model;

    public function __construct(Tenant $model)
    {
        $this->model = $model;
    }

    public function getAllTenants($perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    public function getTenantById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function createTenant(array $tenantData)
    {
        return $this->model->create($tenantData);
    }

    public function updateTenant($id, array $tenantData)
    {
        $tenant = $this->model->findOrFail($id);
        $tenant->update($tenantData);
        return $tenant;
    }

    public function deleteTenant($id)
    {
        return $this->model->findOrFail($id)->delete();
    }
}
