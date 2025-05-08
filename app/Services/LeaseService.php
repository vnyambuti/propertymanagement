<?php
namespace App\Services;

use App\Repositories\Interfaces\LeaseRepositoryInterface;
use App\Repositories\Interfaces\UnitRepositoryInterface;

class LeaseService
{
    protected $leaseRepository;
    protected $unitRepository;

    public function __construct(
        LeaseRepositoryInterface $leaseRepository,
        UnitRepositoryInterface $unitRepository
    ) {
        $this->leaseRepository = $leaseRepository;
        $this->unitRepository = $unitRepository;
    }

    public function getAllLeases($perPage = 15)
    {
        return $this->leaseRepository->getAllLeases($perPage);
    }

    public function getLeaseById($id)
    {
        return $this->leaseRepository->getLeaseById($id);
    }

    public function createLease(array $leaseData)
    {
        // Validate unit is available before creating lease
        $unit = $this->unitRepository->getUnitById($leaseData['unit_id']);

        // Check if unit already has an active lease
        if ($unit->activeLease) {
            throw new \Exception('Unit already has an active lease');
        }

        return $this->leaseRepository->createLease($leaseData);
    }

    public function updateLease($id, array $leaseData)
    {
        return $this->leaseRepository->updateLease($id, $leaseData);
    }

    public function deleteLease($id)
    {
        return $this->leaseRepository->deleteLease($id);
    }

    public function getLeasesByUnit($unitId, $perPage = 15)
    {
        return $this->leaseRepository->getLeasesByUnit($unitId, $perPage);
    }

    public function getLeasesByTenant($tenantId, $perPage = 15)
    {
        return $this->leaseRepository->getLeasesByTenant($tenantId, $perPage);
    }

    public function getActiveLeases($perPage = 15)
    {
        return $this->leaseRepository->getActiveLeases($perPage);
    }

    public function getExpiringLeases($daysThreshold = 30, $perPage = 15)
    {
        return $this->leaseRepository->getExpiringLeases($daysThreshold, $perPage);
    }

    public function terminateLease($id, $terminationReason)
    {
        return $this->leaseRepository->updateLease($id, [
            'status' => 'terminated',
            'notes' => $terminationReason,
        ]);
    }

    public function renewLease($id, $newEndDate, $newRentAmount = null)
    {
        $lease = $this->leaseRepository->getLeaseById($id);

        $updateData = [
            'start_date' => $lease->end_date, // Previous end date becomes new start date
            'end_date' => $newEndDate,
        ];

        if ($newRentAmount) {
            $updateData['rent_amount'] = $newRentAmount;
        }

        return $this->leaseRepository->updateLease($id, $updateData);
    }
}
