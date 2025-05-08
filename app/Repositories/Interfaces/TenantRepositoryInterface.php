<?php
namespace App\Repositories\Interfaces;

interface TenantRepositoryInterface
{
    public function getAllTenants($perPage = 15);
    public function getTenantById($id);
    public function createTenant(array $tenantData);
    public function updateTenant($id, array $tenantData);
    public function deleteTenant($id);
}
