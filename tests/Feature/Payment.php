<?php

namespace Tests\Feature;

use App\Domain\Property\Models\Lease;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->paymentService = Mockery::mock(PaymentService::class);
    $this->app->instance(PaymentService::class, $this->paymentService);
});

afterEach(function () {
    Mockery::close();
});

test('index returns paginated list of payments', function () {
    // Mock data
    $paginatedPayments = [
        'data' => [
            ['id' => 1, 'lease_id' => 1, 'amount' => 1500],
            ['id' => 2, 'lease_id' => 2, 'amount' => 1600],
        ],
        'links' => [],
        'meta' => ['total' => 2]
    ];

    // Set up mock expectations
    $this->paymentService->shouldReceive('getAllPayments')
        ->once()
        ->with(15)
        ->andReturn($paginatedPayments);

    // Make the request
    $response = $this->getJson('/api/v1/payments');

    // Assert response
    $response->assertStatus(200)
        ->assertJson($paginatedPayments);
});

test('show returns a payment by id', function () {
    // Mock data
    $payment = [
        'id' => 1,
        'lease_id' => 1,
        'amount' => 1500,
        'due_date' => '2023-05-01',
        'status' => 'completed'
    ];

    // Set up mock expectations
    $this->paymentService->shouldReceive('getPaymentById')
        ->once()
        ->with(1)
        ->andReturn($payment);

    // Make the request
    $response = $this->getJson('/api/v1/payments/1');

    // Assert response
    $response->assertStatus(200)
        ->assertJson($payment);
});

test('show returns 404 when payment not found', function () {
    // Set up mock expectations
    $this->paymentService->shouldReceive('getPaymentById')
        ->once()
        ->with(999)
        ->andReturn(null);

    // Make the request
    $response = $this->getJson('/api/v1/payments/999');

    // Assert response
    $response->assertStatus(404)
        ->assertJson(['message' => 'Payment not found']);
});

test('store creates a new payment', function () {
    // Mock data
    $paymentData = [
        'lease_id' => 1,
        'amount' => 1500,
        'due_date' => '2023-05-01',
        'status' => 'pending',
        'payment_method' => 'credit_card',
        'notes' => 'Test payment'
    ];

    $createdPayment = array_merge(['id' => 1], $paymentData);

    // Set up mock expectations
    $this->paymentService->shouldReceive('createPayment')
        ->once()
        ->with(Mockery::on(function ($data) use ($paymentData) {
            return $data == $paymentData;
        }))
        ->andReturn($createdPayment);

    // Mock DB tables for validation
    $this->mock('Illuminate\Database\Schema\Builder', function ($mock) {
        $mock->shouldReceive('hasTable')->andReturn(true);
    });

    // Create test records for foreign key checks
    Lease::factory()->create(['id' => 1]);

    // Make the request
    $response = $this->postJson('/api/v1/payments', $paymentData);

    // Assert response
    $response->assertStatus(201)
        ->assertJson($createdPayment);
});

test('store validates input data', function () {
    // Make the request with incomplete data
    $response = $this->postJson('/api/v1/payments', [
        'lease_id' => 1
        // Missing required fields
    ]);

    // Assert validation failure
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['amount', 'due_date', 'status', 'payment_method']);
});

test('update modifies an existing payment', function () {
    // Mock data
    $paymentId = 1;
    $updateData = [
        'amount' => 1600,
        'status' => 'completed',
        'payment_date' => '2023-05-01',
        'notes' => 'Updated notes'
    ];

    $updatedPayment = [
        'id' => $paymentId,
        'lease_id' => 1,
        'amount' => 1600,
        'due_date' => '2023-05-01',
        'status' => 'completed',
        'payment_method' => 'credit_card',
        'payment_date' => '2023-05-01',
        'notes' => 'Updated notes'
    ];

    // Set up mock expectations
    $this->paymentService->shouldReceive('updatePayment')
        ->once()
        ->with($paymentId, Mockery::on(function ($data) use ($updateData) {
            return $data == $updateData;
        }))
        ->andReturn($updatedPayment);

    // Make the request
    $response = $this->putJson("/api/v1/payments/{$paymentId}", $updateData);

    // Assert response
    $response->assertStatus(200)
        ->assertJson($updatedPayment);
});

test('update returns 404 when payment not found', function () {
    // Set up mock expectations
    $this->paymentService->shouldReceive('updatePayment')
        ->once()
        ->with(999, Mockery::any())
        ->andReturn(null);

    // Make the request
    $response = $this->putJson('/api/v1/payments/999', [
        'amount' => 1600
    ]);

    // Assert response
    $response->assertStatus(404)
        ->assertJson(['message' => 'Payment not found']);
});

test('destroy deletes a payment', function () {
    // Set up mock expectations
    $this->paymentService->shouldReceive('deletePayment')
        ->once()
        ->with(1)
        ->andReturn(true);

    // Make the request
    $response = $this->deleteJson('/api/v1/payments/1');

    // Assert response
    $response->assertStatus(200)
        ->assertJson(['message' => 'Payment deleted successfully']);
});

test('destroy returns 404 when payment not found', function () {
    // Set up mock expectations
    $this->paymentService->shouldReceive('deletePayment')
        ->once()
        ->with(999)
        ->andReturn(false);

    // Make the request
    $response = $this->deleteJson('/api/v1/payments/999');

    // Assert response
    $response->assertStatus(404)
        ->assertJson(['message' => 'Payment not found']);
});

test('getPaymentsByLease returns payments for a specific lease', function () {
    // Mock data
    $leaseId = 1;
    $paginatedPayments = [
        'data' => [
            ['id' => 1, 'lease_id' => $leaseId, 'amount' => 1500],
            ['id' => 2, 'lease_id' => $leaseId, 'amount' => 1500],
        ],
        'links' => [],
        'meta' => ['total' => 2]
    ];

    // Set up mock expectations
    $this->paymentService->shouldReceive('getPaymentsByLease')
        ->once()
        ->with($leaseId, 15)
        ->andReturn($paginatedPayments);

    // Make the request
    $response = $this->getJson("/api/v1/payments/lease/{$leaseId}");

    // Assert response
    $response->assertStatus(200)
        ->assertJson($paginatedPayments);
});

test('getPaymentsByDateRange returns payments within a date range', function () {
    // Mock data
    $startDate = '2023-05-01';
    $endDate = '2023-05-31';
    $paginatedPayments = [
        'data' => [
            ['id' => 1, 'due_date' => '2023-05-01'],
            ['id' => 2, 'due_date' => '2023-05-15'],
        ],
        'links' => [],
        'meta' => ['total' => 2]
    ];

    // Set up mock expectations
    $this->paymentService->shouldReceive('getPaymentsByDateRange')
        ->once()
        ->with($startDate, $endDate, 15)
        ->andReturn($paginatedPayments);

    // Make the request
    $response = $this->getJson("/api/v1/payments/date-range?start_date={$startDate}&end_date={$endDate}");

    // Assert response
    $response->assertStatus(200)
        ->assertJson($paginatedPayments);
});

test('getPaymentsByDateRange validates date inputs', function () {
    // Make request with invalid date range
    $response = $this->getJson('/api/v1/payments/date-range?start_date=2023-05-31&end_date=2023-05-01');

    // Assert validation failure
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['end_date']);
});

test('getOutstandingPayments returns unpaid payments', function () {
    // Mock data
    $paginatedPayments = [
        'data' => [
            ['id' => 1, 'status' => 'pending'],
            ['id' => 2, 'status' => 'overdue'],
        ],
        'links' => [],
        'meta' => ['total' => 2]
    ];

    // Set up mock expectations
    $this->paymentService->shouldReceive('getOutstandingPayments')
        ->once()
        ->with(15)
        ->andReturn($paginatedPayments);

    // Make the request
    $response = $this->getJson('/api/v1/payments/outstanding');

    // Assert response
    $response->assertStatus(200)
        ->assertJson($paginatedPayments);
});

test('markPaymentAsCompleted marks a payment as completed', function () {
    // Mock data
    $paymentId = 1;
    $completedPayment = [
        'id' => $paymentId,
        'status' => 'completed',
        'payment_date' => Carbon::now()->toDateString()
    ];

    // Set up mock expectations
    $this->paymentService->shouldReceive('markPaymentAsCompleted')
        ->once()
        ->with($paymentId)
        ->andReturn($completedPayment);

    // Make the request
    $response = $this->postJson("/api/v1/payments/{$paymentId}/complete");

    // Assert response
    $response->assertStatus(200)
        ->assertJson($completedPayment);
});

test('markPaymentAsCompleted returns 404 when payment not found', function () {
    // Set up mock expectations
    $this->paymentService->shouldReceive('markPaymentAsCompleted')
        ->once()
        ->with(999)
        ->andReturn(null);

    // Make the request
    $response = $this->postJson('/api/v1/payments/999/complete');

    // Assert response
    $response->assertStatus(404)
        ->assertJson(['message' => 'Payment not found']);
});

test('generateRentInvoice creates a rent invoice for a lease', function () {
    // Mock data
    $leaseId = 1;
    $invoice = [
        'id' => 1,
        'lease_id' => $leaseId,
        'amount' => 1500,
        'due_date' => Carbon::now()->addDays(5)->toDateString(),
        'status' => 'pending'
    ];

    // Set up mock expectations
    $this->paymentService->shouldReceive('generateRentInvoice')
        ->once()
        ->with($leaseId)
        ->andReturn($invoice);

    // Mock DB tables for validation
    $this->mock('Illuminate\Database\Schema\Builder', function ($mock) {
        $mock->shouldReceive('hasTable')->andReturn(true);
    });

    // Create test records for foreign key checks
    Lease::factory()->create(['id' => 1]);

    // Make the request
    $response = $this->postJson('/api/v1/payments/generate-invoice', [
        'lease_id' => $leaseId
    ]);

    // Assert response
    $response->assertStatus(201)
        ->assertJson($invoice);
});

test('generateRentInvoice returns error when service fails', function () {
    // Set up mock expectations
    $this->paymentService->shouldReceive('generateRentInvoice')
        ->once()
        ->with(1)
        ->andThrow(new \Exception('Cannot generate invoice for an inactive lease'));

    // Mock DB tables for validation
    $this->mock('Illuminate\Database\Schema\Builder', function ($mock) {
        $mock->shouldReceive('hasTable')->andReturn(true);
    });

    // Create test records for foreign key checks
    Lease::factory()->create(['id' => 1]);

    // Make the request
    $response = $this->postJson('/api/v1/payments/generate-invoice', [
        'lease_id' => 1
    ]);

    // Assert response
    $response->assertStatus(400)
        ->assertJson(['message' => 'Cannot generate invoice for an inactive lease']);
});
