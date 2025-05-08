<?php
namespace App\Services;

use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Repositories\Interfaces\LeaseRepositoryInterface;

class PaymentService
{
    protected $paymentRepository;
    protected $leaseRepository;

    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        LeaseRepositoryInterface $leaseRepository
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->leaseRepository = $leaseRepository;
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
}
