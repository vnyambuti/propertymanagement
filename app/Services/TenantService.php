<?php
namespace App\Services;

use App\Repositories\Interfaces\TenantRepositoryInterface;

class TenantService
{
    protected $tenantRepository;

    public function __construct(TenantRepositoryInterface $tenantRepository)
    {
        $this->tenantRepository = $tenantRepository;
    }

    public function getAllTenants($perPage = 15)
    {
        return $this->tenantRepository->getAllTenants($perPage);
    }

    public function getTenantById($id)
    {
        return $this->tenantRepository->getTenantById($id);
    }

    public function createTenant(array $tenantData)
    {
        return $this->tenantRepository->createTenant($tenantData);
    }

    public function updateTenant($id, array $tenantData)
    {
        return $this->tenantRepository->updateTenant($id, $tenantData);
    }

    public function deleteTenant($id)
    {
        return $this->tenantRepository->deleteTenant($id);
    }
}
