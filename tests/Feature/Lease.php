<?php

namespace Tests\Feature;

use App\Domain\Property\Models\Tenant;
use App\Domain\Property\Models\Unit;
use App\Services\LeaseService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->leaseService = Mockery::mock(LeaseService::class);
    $this->app->instance(LeaseService::class, $this->leaseService);
});

afterEach(function () {
    Mockery::close();
});

test('index returns paginated list of leases', function () {
    // Mock data
    $paginatedLeases = [
        'data' => [
            ['id' => 1, 'unit_id' => 1, 'tenant_id' => 1],
            ['id' => 2, 'unit_id' => 2, 'tenant_id' => 2],
        ],
        'links' => [],
        'meta' => ['total' => 2]
    ];

    // Set up mock expectations
    $this->leaseService->shouldReceive('getAllLeases')
        ->once()
        ->with(15)
        ->andReturn($paginatedLeases);

    // Make the request
    $response = $this->getJson('/api/v1/leases');

    // Assert response
    $response->assertStatus(200)
        ->assertJson($paginatedLeases);
});

test('show returns a lease by id', function () {
    // Mock data
    $lease = [
        'id' => 1,
        'unit_id' => 1,
        'tenant_id' => 1,
        'start_date' => '2023-01-01',
        'end_date' => '2023-12-31'
    ];

    // Set up mock expectations
    $this->leaseService->shouldReceive('getLeaseById')
        ->once()
        ->with(1)
        ->andReturn($lease);

    // Make the request
    $response = $this->getJson('/api/v1/leases/1');

    // Assert response
    $response->assertStatus(200)
        ->assertJson($lease);
});

test('show returns 404 when lease not found', function () {
    // Set up mock expectations
    $this->leaseService->shouldReceive('getLeaseById')
        ->once()
        ->with(999)
        ->andReturn(null);

    // Make the request
    $response = $this->getJson('/api/v1/leases/999');

    // Assert response
    $response->assertStatus(404)
        ->assertJson(['message' => 'Lease not found']);
});

test('store creates a new lease', function () {
    // Mock data
    $leaseData = [
        'unit_id' => 1,
        'tenant_id' => 1,
        'start_date' => '2023-01-01',
        'end_date' => '2023-12-31',
        'rent_amount' => 1500,
        'security_deposit' => 1500,
        'status' => 'active',
        'notes' => 'Test notes'
    ];

    $createdLease = array_merge(['id' => 1], $leaseData);

    // Set up mock expectations
    $this->leaseService->shouldReceive('createLease')
        ->once()
        ->with(Mockery::on(function ($data) use ($leaseData) {
            return $data == $leaseData;
        }))
        ->andReturn($createdLease);

    // Mock DB tables for validation
    $this->mock('Illuminate\Database\Schema\Builder', function ($mock) {
        $mock->shouldReceive('hasTable')->andReturn(true);
    });

    // Create test records for foreign key checks
    Unit::factory()->create(['id' => 1]);
    Tenant::factory()->create(['id' => 1]);

    // Make the request
    $response = $this->postJson('/api/v1/leases', $leaseData);

    // Assert response
    $response->assertStatus(201)
        ->assertJson($createdLease);
});

test('store validates input data', function () {
    // Make the request with incomplete data
    $response = $this->postJson('/api/v1/leases', [
        'unit_id' => 1
        // Missing required fields
    ]);

    // Assert validation failure
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['tenant_id', 'start_date', 'end_date',
            'rent_amount', 'security_deposit', 'status']);
});

test('update modifies an existing lease', function () {
    // Mock data
    $leaseId = 1;
    $updateData = [
        'rent_amount' => 1600,
        'status' => 'pending',
        'notes' => 'Updated notes'
    ];

    $updatedLease = [
        'id' => $leaseId,
        'unit_id' => 1,
        'tenant_id' => 1,
        'start_date' => '2023-01-01',
        'end_date' => '2023-12-31',
        'rent_amount' => 1600,
        'security_deposit' => 1500,
        'status' => 'pending',
        'notes' => 'Updated notes'
    ];

    // Set up mock expectations
    $this->leaseService->shouldReceive('updateLease')
        ->once()
        ->with($leaseId, Mockery::on(function ($data) use ($updateData) {
            return $data == $updateData;
        }))
        ->andReturn($updatedLease);

    // Make the request
    $response = $this->putJson("/api/v1/leases/{$leaseId}", $updateData);

    // Assert response
    $response->assertStatus(200)
        ->assertJson($updatedLease);
});

test('update returns 404 when lease not found', function () {
    // Set up mock expectations
    $this->leaseService->shouldReceive('updateLease')
        ->once()
        ->with(999, Mockery::any())
        ->andReturn(null);

    // Make the request
    $response = $this->putJson('/api/v1/leases/999', [
        'rent_amount' => 1600
    ]);

    // Assert response
    $response->assertStatus(404)
        ->assertJson(['message' => 'Lease not found']);
});

test('destroy deletes a lease', function () {
    // Set up mock expectations
    $this->leaseService->shouldReceive('deleteLease')
        ->once()
        ->with(1)
        ->andReturn(true);

    // Make the request
    $response = $this->deleteJson('/api/v1/leases/1');

    // Assert response
    $response->assertStatus(200)
        ->assertJson(['message' => 'Lease deleted successfully']);
});

test('destroy returns 404 when lease not found', function () {
    // Set up mock expectations
    $this->leaseService->shouldReceive('deleteLease')
        ->once()
        ->with(999)
        ->andReturn(false);

    // Make the request
    $response = $this->deleteJson('/api/v1/leases/999');

    // Assert response
    $response->assertStatus(404)
        ->assertJson(['message' => 'Lease not found']);
});

test('getLeasesByUnit returns leases for a specific unit', function () {
    // Mock data
    $unitId = 1;
    $paginatedLeases = [
        'data' => [
            ['id' => 1, 'unit_id' => $unitId, 'tenant_id' => 1],
            ['id' => 3, 'unit_id' => $unitId, 'tenant_id' => 3],
        ],
        'links' => [],
        'meta' => ['total' => 2]
    ];

    // Set up mock expectations
    $this->leaseService->shouldReceive('getLeasesByUnit')
        ->once()
        ->with($unitId, 15)
        ->andReturn($paginatedLeases);

    // Make the request
    $response = $this->getJson("/api/v1/leases/unit/{$unitId}");

    // Assert response
    $response->assertStatus(200)
        ->assertJson($paginatedLeases);
});

test('getLeasesByTenant returns leases for a specific tenant', function () {
    // Mock data
    $tenantId = 1;
    $paginatedLeases = [
        'data' => [
            ['id' => 1, 'unit_id' => 1, 'tenant_id' => $tenantId],
            ['id' => 4, 'unit_id' => 4, 'tenant_id' => $tenantId],
        ],
        'links' => [],
        'meta' => ['total' => 2]
    ];

    // Set up mock expectations
    $this->leaseService->shouldReceive('getLeasesByTenant')
        ->once()
        ->with($tenantId, 15)
        ->andReturn($paginatedLeases);

    // Make the request
    $response = $this->getJson("/api/v1/leases/tenant/{$tenantId}");

    // Assert response
    $response->assertStatus(200)
        ->assertJson($paginatedLeases);
});

test('getActiveLeases returns active leases', function () {
    // Mock data
    $paginatedLeases = [
        'data' => [
            ['id' => 1, 'status' => 'active'],
            ['id' => 2, 'status' => 'active'],
        ],
        'links' => [],
        'meta' => ['total' => 2]
    ];

    // Set up mock expectations
    $this->leaseService->shouldReceive('getActiveLeases')
        ->once()
        ->with(15)
        ->andReturn($paginatedLeases);

    // Make the request
    $response = $this->getJson('/api/v1/leases/status/active');

    // Assert response
    $response->assertStatus(200)
        ->assertJson($paginatedLeases);
});

test('getExpiringLeases returns leases expiring soon', function () {
    // Mock data
    $daysThreshold = 30;
    $paginatedLeases = [
        'data' => [
            ['id' => 1, 'end_date' => Carbon::now()->addDays(15)->toDateString()],
            ['id' => 2, 'end_date' => Carbon::now()->addDays(25)->toDateString()],
        ],
        'links' => [],
        'meta' => ['total' => 2]
    ];

    // Set up mock expectations
    $this->leaseService->shouldReceive('getExpiringLeases')
        ->once()
        ->with($daysThreshold, 15)
        ->andReturn($paginatedLeases);

    // Make the request
    $response = $this->getJson('/api/v1/leases/expiring?days=30');

    // Assert response
    $response->assertStatus(200)
        ->assertJson($paginatedLeases);
});

test('terminateLease terminates a lease', function () {
    // Mock data
    $leaseId = 1;
    $terminationReason = 'Tenant request';
    $terminatedLease = [
        'id' => $leaseId,
        'status' => 'terminated',
        'termination_reason' => $terminationReason
    ];

    // Set up mock expectations
    $this->leaseService->shouldReceive('terminateLease')
        ->once()
        ->with($leaseId, $terminationReason)
        ->andReturn($terminatedLease);

    // Make the request
    $response = $this->postJson("/api/v1/leases/{$leaseId}/terminate", [
        'termination_reason' => $terminationReason
    ]);

    // Assert response
    $response->assertStatus(200)
        ->assertJson($terminatedLease);
});

test('terminateLease returns 404 when lease not found', function () {
    // Set up mock expectations
    $this->leaseService->shouldReceive('terminateLease')
        ->once()
        ->with(999, 'Tenant request')
        ->andReturn(null);

    // Make the request
    $response = $this->postJson('/api/v1/leases/999/terminate', [
        'termination_reason' => 'Tenant request'
    ]);

    // Assert response
    $response->assertStatus(404)
        ->assertJson(['message' => 'Lease not found']);
});

test('renewLease renews a lease', function () {
    // Mock data
    $leaseId = 1;
    $newEndDate = '2024-12-31';
    $newRentAmount = 1700;
    $renewedLease = [
        'id' => $leaseId,
        'end_date' => $newEndDate,
        'rent_amount' => $newRentAmount,
        'status' => 'active'
    ];

    // Set up mock expectations
    $this->leaseService->shouldReceive('renewLease')
        ->once()
        ->with($leaseId, $newEndDate, $newRentAmount)
        ->andReturn($renewedLease);

    // Make the request
    $response = $this->postJson("/api/v1/leases/{$leaseId}/renew", [
        'new_end_date' => $newEndDate,
        'new_rent_amount' => $newRentAmount
    ]);

    // Assert response
    $response->assertStatus(200)
        ->assertJson($renewedLease);
});

test('renewLease returns 404 when lease not found', function () {
    // Set up mock expectations
    $this->leaseService->shouldReceive('renewLease')
        ->once()
        ->with(999, '2024-12-31', null)
        ->andReturn(null);

    // Make the request
    $response = $this->postJson('/api/v1/leases/999/renew', [
        'new_end_date' => '2024-12-31'
    ]);

    // Assert response
    $response->assertStatus(404)
        ->assertJson(['message' => 'Lease not found']);
});
