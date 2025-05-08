<?php
namespace App\Repositories;

use App\Domain\Property\Models\Lease;
use Carbon\Carbon;
use App\Repositories\Interfaces\LeaseRepositoryInterface;

class LeaseRepository implements LeaseRepositoryInterface
{
    protected $model;

    public function __construct(Lease $model)
    {
        $this->model = $model;
    }

    public function getAllLeases($perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    public function getLeaseById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function createLease(array $leaseData)
    {
        return $this->model->create($leaseData);
    }

    public function updateLease($id, array $leaseData)
    {
        $lease = $this->model->findOrFail($id);
        $lease->update($leaseData);
        return $lease;
    }

    public function deleteLease($id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function getLeasesByUnit($unitId, $perPage = 15)
    {
        return $this->model->where('unit_id', $unitId)->paginate($perPage);
    }

    public function getLeasesByTenant($tenantId, $perPage = 15)
    {
        return $this->model->where('tenant_id', $tenantId)->paginate($perPage);
    }

    public function getActiveLeases($perPage = 15)
    {
        return $this->model->active()->paginate($perPage);
    }

    public function getExpiringLeases($daysThreshold = 30, $perPage = 15)
    {
        $futureDate = Carbon::now()->addDays($daysThreshold);
        return $this->model->active()
            ->whereDate('end_date', '<=', $futureDate)
            ->whereDate('end_date', '>=', Carbon::now())
            ->paginate($perPage);
    }
}
