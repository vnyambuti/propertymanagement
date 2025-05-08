<?php
namespace App\Repositories\Interfaces;

interface LeaseRepositoryInterface
{
    public function getAllLeases($perPage = 15);
    public function getLeaseById($id);
    public function createLease(array $leaseData);
    public function updateLease($id, array $leaseData);
    public function deleteLease($id);
    public function getLeasesByUnit($unitId, $perPage = 15);
    public function getLeasesByTenant($tenantId, $perPage = 15);
    public function getActiveLeases($perPage = 15);
    public function getExpiringLeases($daysThreshold = 30, $perPage = 15);
}
