<?php
namespace App\Repositories;

use App\Domain\Property\Models\Payment;
use Carbon\Carbon;
use App\Repositories\Interfaces\PaymentRepositoryInterface;

class PaymentRepository implements PaymentRepositoryInterface
{
    protected $model;

    public function __construct(Payment $model)
    {
        $this->model = $model;
    }

    public function getAllPayments($perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    public function getPaymentById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function createPayment(array $paymentData)
    {
        return $this->model->create($paymentData);
    }

    public function updatePayment($id, array $paymentData)
    {
        $payment = $this->model->findOrFail($id);
        $payment->update($paymentData);
        return $payment;
    }

    public function deletePayment($id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function getPaymentsByLease($leaseId, $perPage = 15)
    {
        return $this->model->where('lease_id', $leaseId)->paginate($perPage);
    }

    public function getPaymentsByDateRange($startDate, $endDate, $perPage = 15)
    {
        return $this->model->whereBetween('payment_date', [$startDate, $endDate])->paginate($perPage);
    }

    public function getOutstandingPayments($perPage = 15)
    {
        return $this->model->where('status', '!=', 'completed')->paginate($perPage);
    }
}
