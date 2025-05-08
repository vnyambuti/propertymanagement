<?php
namespace App\Repositories\Interfaces;

interface PaymentRepositoryInterface
{
    public function getAllPayments($perPage = 15);
    public function getPaymentById($id);
    public function createPayment(array $paymentData);
    public function updatePayment($id, array $paymentData);
    public function deletePayment($id);
    public function getPaymentsByLease($leaseId, $perPage = 15);
    public function getPaymentsByDateRange($startDate, $endDate, $perPage = 15);
    public function getOutstandingPayments($perPage = 15);
}
