<?php

use App\Http\Controllers\Api\v1\Property\IndexController;
use App\Services\PropertyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

// use Tests\TestCase;

// uses(TestCase::class);

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->propertyService = Mockery::mock(PropertyService::class);
    $this->controller = new IndexController($this->propertyService);
});

afterEach(function () {
    Mockery::close();
});

// Index Tests
test('index returns paginated properties', function () {
    $mockProperties = collect([
        (object)['id' => 1, 'name' => 'Property 1'],
        (object)['id' => 2, 'name' => 'Property 2'],
    ]);

    $request = Request::create('/api/v1/properties', 'GET', ['per_page' => 10]);

    $this->propertyService->shouldReceive('getAllProperties')
        ->once()
        ->with(10)
        ->andReturn($mockProperties);

    $response = $this->controller->index($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($responseData)->toHaveKey('success', true)
        ->and($responseData)->toHaveKey('data')
        ->and($responseData['data'])->toHaveCount(2);
});

test('index uses default pagination if not specified', function () {
    $mockProperties = collect([
        (object)['id' => 1, 'name' => 'Property 1'],
    ]);

    $request = Request::create('/api/v1/properties', 'GET');

    $this->propertyService->shouldReceive('getAllProperties')
        ->once()
        ->with(15) // Default value
        ->andReturn($mockProperties);

    $response = $this->controller->index($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($responseData)->toHaveKey('success', true);
});

// Store Tests
test('store creates a new property with valid data', function () {
    $propertyData = [
        'name' => 'New Property',
        'address' => '123 Main St',
        'town' => 'Townsville',
        'county' => 'County',
        'type' => 'Apartment',
        'user_id' => 1,
    ];

    $request = Request::create('/api/v1/properties', 'POST', $propertyData);

    $expectedResult = (object)array_merge(['id' => 1], $propertyData);

    $this->propertyService->shouldReceive('createProperty')
        ->once()
        ->with($propertyData)
        ->andReturn($expectedResult);

    $response = $this->controller->store($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(201)
        ->and($responseData)->toHaveKey('success', true)
        ->and($responseData)->toHaveKey('data')
        ->and($responseData['data']->name)->toBe('New Property');
});

test('store validates input and returns errors for invalid data', function () {
    $invalidData = [
        'name' => 'New Property',
        // Missing other required fields
    ];

    $request = Request::create('/api/v1/properties', 'POST', $invalidData);

    $response = $this->controller->store($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(422)
        ->and($responseData)->toHaveKey('success', false)
        ->and($responseData)->toHaveKey('errors');
});

// Show Tests
test('show returns property details for valid ID', function () {
    $expectedProperty = (object)[
        'id' => 1,
        'name' => 'Test Property',
        'address' => '123 Main St',
        'town' => 'Townsville',
        'county' => 'County',
        'type' => 'Apartment',
        'user_id' => 1,
    ];

    $this->propertyService->shouldReceive('getPropertyById')
        ->once()
        ->with(1)
        ->andReturn($expectedProperty);

    $response = $this->controller->show(1);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($responseData)->toHaveKey('success', true)
        ->and($responseData)->toHaveKey('data')
        ->and($responseData['data']->id)->toBe(1);
});

test('show returns 404 for non-existent property', function () {
    $this->propertyService->shouldReceive('getPropertyById')
        ->once()
        ->with(999)
        ->andThrow(new Exception('Property not found'));

    $response = $this->controller->show(999);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(404)
        ->and($responseData)->toHaveKey('success', false)
        ->and($responseData)->toHaveKey('message', 'Property not found');
});

// Update Tests
test('update modifies property with valid data', function () {
    $updateData = [
        'name' => 'Updated Property',
        'address' => '456 New St',
    ];

    $request = Request::create('/api/v1/properties/1', 'PUT', $updateData);

    $updatedProperty = (object)[
        'id' => 1,
        'name' => 'Updated Property',
        'address' => '456 New St',
        'town' => 'Townsville',
        'county' => 'County',
        'type' => 'Apartment',
        'user_id' => 1,
    ];

    $this->propertyService->shouldReceive('updateProperty')
        ->once()
        ->with(1, $updateData)
        ->andReturn($updatedProperty);

    $response = $this->controller->update($request, 1);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($responseData)->toHaveKey('success', true)
        ->and($responseData)->toHaveKey('data')
        ->and($responseData['data']->name)->toBe('Updated Property');
});

test('update validates input and returns errors for invalid data', function () {
    $invalidData = [
        'user_id' => 'not-a-number', // Invalid user_id
    ];

    $request = Request::create('/api/v1/properties/1', 'PUT', $invalidData);

    $response = $this->controller->update($request, 1);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(422)
        ->and($responseData)->toHaveKey('success', false)
        ->and($responseData)->toHaveKey('errors');
});

test('update returns 404 for non-existent property', function () {
    $updateData = [
        'name' => 'Updated Property',
    ];

    $request = Request::create('/api/v1/properties/999', 'PUT', $updateData);

    $this->propertyService->shouldReceive('updateProperty')
        ->once()
        ->with(999, $updateData)
        ->andThrow(new Exception('Property not found'));

    $response = $this->controller->update($request, 999);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(404)
        ->and($responseData)->toHaveKey('success', false)
        ->and($responseData)->toHaveKey('message', 'Property not found');
});

// Destroy Tests
test('destroy deletes property with valid ID', function () {
    $this->propertyService->shouldReceive('deleteProperty')
        ->once()
        ->with(1)
        ->andReturn(true);

    $response = $this->controller->destroy(1);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($responseData)->toHaveKey('success', true)
        ->and($responseData)->toHaveKey('message', 'Property deleted successfully');
});

test('destroy returns 404 for non-existent property', function () {
    $this->propertyService->shouldReceive('deleteProperty')
        ->once()
        ->with(999)
        ->andThrow(new Exception('Property not found'));

    $response = $this->controller->destroy(999);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(404)
        ->and($responseData)->toHaveKey('success', false)
        ->and($responseData)->toHaveKey('message', 'Property not found');
});

// GetByUser Tests
test('getByUser returns properties for a specific user', function () {
    $userId = 1;
    $perPage = 10;
    $mockProperties = collect([
        (object)['id' => 1, 'name' => 'Property 1', 'user_id' => $userId],
        (object)['id' => 2, 'name' => 'Property 2', 'user_id' => $userId],
    ]);

    $request = Request::create("/api/v1/users/{$userId}/properties", 'GET', ['per_page' => $perPage]);

    $this->propertyService->shouldReceive('getPropertiesByUser')
        ->once()
        ->with($userId, $perPage)
        ->andReturn($mockProperties);

    $response = $this->controller->getByUser($request, $userId);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($responseData)->toHaveKey('success', true)
        ->and($responseData)->toHaveKey('data')
        ->and($responseData['data'])->toHaveCount(2);
});

test('getByUser uses default pagination if not specified', function () {
    $userId = 1;
    $mockProperties = collect([
        (object)['id' => 1, 'name' => 'Property 1', 'user_id' => $userId],
    ]);

    $request = Request::create("/api/v1/users/{$userId}/properties", 'GET');

    $this->propertyService->shouldReceive('getPropertiesByUser')
        ->once()
        ->with($userId, 15) // Default value
        ->andReturn($mockProperties);

    $response = $this->controller->getByUser($request, $userId);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($responseData)->toHaveKey('success', true);
});
