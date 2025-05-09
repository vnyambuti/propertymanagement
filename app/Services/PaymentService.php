<?php
namespace App\Services;

use App\Domain\Property\Models\Lease;
use App\Domain\Property\Models\Payment;
use App\Domain\Property\Models\Property;
use App\Domain\Property\Models\Tenant;
use App\Domain\Property\Models\Unit;
use App\Jobs\SendPaymentReceipt;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Repositories\Interfaces\LeaseRepositoryInterface;
use App\Repositories\Interfaces\PropertyRepositoryInterface;
use App\Repositories\Interfaces\TenantRepositoryInterface;
use App\Repositories\Interfaces\UnitRepositoryInterface;


class PaymentService
{
    protected $paymentRepository;
    protected $leaseRepository;
    protected $tenantRepository;
    protected $unitRepository;
    protected $propertyRepository;

    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        LeaseRepositoryInterface $leaseRepository,
        TenantRepositoryInterface $tenantRepository,
        UnitRepositoryInterface $unitRepository,
        PropertyRepositoryInterface $propertyRepository

    ) {
        $this->paymentRepository = $paymentRepository;
        $this->leaseRepository = $leaseRepository;
        $this->tenantRepository=$tenantRepository;
        $this->unitRepository=$unitRepository;
        $this->propertyRepository=$propertyRepository;

    }

    public function getAllPayments($perPage = 15)
    {
        return $this->paymentRepository->getAllPayments($perPage);
    }

    public function getPaymentById($id)
    {
        return $this->paymentRepository->getPaymentById($id);
    }

    public function createPayment(array $paymentData)
    {
        // Verify lease exists and is active
        $lease = $this->leaseRepository->getLeaseById($paymentData['lease_id']);
        if (!$lease->isActive()) {
            throw new \Exception('Cannot record payment for inactive lease');
        }

        return $this->paymentRepository->createPayment($paymentData);
    }

    public function updatePayment($id, array $paymentData)
    {
        return $this->paymentRepository->updatePayment($id, $paymentData);
    }

    public function deletePayment($id)
    {
        return $this->paymentRepository->deletePayment($id);
    }

    public function getPaymentsByLease($leaseId, $perPage = 15)
    {
        return $this->paymentRepository->getPaymentsByLease($leaseId, $perPage);
    }

    public function getPaymentsByDateRange($startDate, $endDate, $perPage = 15)
    {
        return $this->paymentRepository->getPaymentsByDateRange($startDate, $endDate, $perPage);
    }

    public function getOutstandingPayments($perPage = 15)
    {
        return $this->paymentRepository->getOutstandingPayments($perPage);
    }

    public function markPaymentAsCompleted($id)
    {
        return $this->paymentRepository->updatePayment($id, [
            'status' => 'completed',
            'payment_date' => now()
        ]);
    }

    public function generateRentInvoice($leaseId)
    {
        $lease = $this->leaseRepository->getLeaseById($leaseId);

        if (!$lease->isActive()) {
            throw new \Exception('Cannot generate invoice for inactive lease');
        }

        $dueDate = now()->addDays(7); // Due in 7 days

        return $this->paymentRepository->createPayment([
            'lease_id' => $leaseId,
            'amount' => $lease->rent_amount,
            'due_date' => $dueDate,
            'status' => 'pending',
            'payment_method' => 'pending',
            'notes' => 'Monthly rent invoice'
        ]);
    }

    public function sendPaymentReceipt(int $paymentId): bool
    {
        $payment = $this->paymentRepository->getPaymentById($paymentId);

        if (!$payment || $payment->status !== 'completed') {
            return false;
        }

        // Get the related data
        $lease = $this->leaseRepository->getLeaseById($payment->lease_id);;
        $tenant = $this->tenantRepository->getTenantById($lease->tenant_id);
        $unit = $this->unitRepository->getUnitById($lease->unit_id);
        $property = $this->propertyRepository->getPropertyById($unit->property_id);

        // Dispatch the job to the queue
        SendPaymentReceipt::dispatch(
            $payment,
            $tenant,
            $property,
            $unit,
            $tenant->email
        );

        return true;
    }

}
