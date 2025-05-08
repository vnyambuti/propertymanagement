<?php

use App\Http\Controllers\Api\v1\Auth\AuthController;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


use Tests\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->authService = Mockery::mock(AuthService::class);
    $this->controller = new AuthController($this->authService);
});

afterEach(function () {
    Mockery::close();
});

// Register Tests
test('register validates input correctly', function () {
    $request = Request::create('/api/v1/auth/register', 'POST', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->authService->shouldReceive('register')
        ->once()
        ->andReturn([
            'user' => (object)['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com', 'role' => 'user'],
            'token' => 'test-token',
            'token_type' => 'Bearer'
        ]);

    $response = $this->controller->register($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(201)
        ->and($responseData)->toHaveKey('message')
        ->and($responseData)->toHaveKey('user')
        ->and($responseData)->toHaveKey('token')
        ->and($responseData['user'])->toHaveKey('name', 'Test User');
});

test('register returns validation error when data is invalid', function () {
    $request = Request::create('/api/v1/auth/register', 'POST', [
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => 'short',
    ]);

    $response = $this->controller->register($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(422)
        ->and($responseData)->toHaveKey('message', 'Validation failed')
        ->and($responseData)->toHaveKey('errors');
});

test('register sets default role to user if not provided', function () {
    $request = Request::create('/api/v1/auth/register', 'POST', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->authService->shouldReceive('register')
        ->once()
        ->withArgs(function ($userData) {
            return $userData['role'] === 'user';
        })
        ->andReturn([
            'user' => (object)['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com', 'role' => 'user'],
            'token' => 'test-token',
            'token_type' => 'Bearer'
        ]);

    $response = $this->controller->register($request);
    expect($response->getStatusCode())->toBe(201);
});

// Login Tests
test('login validates input correctly', function () {
    $request = Request::create('/api/v1/auth/login', 'POST', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $this->authService->shouldReceive('login')
        ->once()
        ->with('test@example.com', 'password123')
        ->andReturn([
            'user' => (object)['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com'],
            'token' => 'test-token',
            'token_type' => 'Bearer'
        ]);

    $response = $this->controller->login($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($responseData)->toHaveKey('message')
        ->and($responseData)->toHaveKey('user')
        ->and($responseData)->toHaveKey('token');
});

test('login returns validation error when data is invalid', function () {
    $request = Request::create('/api/v1/auth/login', 'POST', [
        'email' => 'invalid-email',
    ]);

    $response = $this->controller->login($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(422)
        ->and($responseData)->toHaveKey('message', 'Validation failed')
        ->and($responseData)->toHaveKey('errors');
});

test('login handles invalid credentials', function () {
    $request = Request::create('/api/v1/auth/login', 'POST', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $this->authService->shouldReceive('login')
        ->once()
        ->with('test@example.com', 'wrong-password')
        ->andThrow(new Exception('Invalid credentials'));

    $response = $this->controller->login($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(401)
        ->and($responseData)->toHaveKey('message', 'Invalid credentials');
});

// Logout Tests
test('logout calls authService and returns success', function () {
    $request = Request::create('/api/v1/auth/logout', 'POST');

    $this->authService->shouldReceive('logout')
        ->once()
        ->with($request)
        ->andReturn(true);

    $response = $this->controller->logout($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($responseData)->toHaveKey('message', 'User logged out successfully');
});

// Forgot Password Tests
test('forgotPassword validates input correctly', function () {
    $request = Request::create('/api/v1/auth/forgot-password', 'POST', [
        'email' => 'test@example.com',
    ]);

    $this->authService->shouldReceive('forgotPassword')
        ->once()
        ->with('test@example.com')
        ->andReturn(true);

    $response = $this->controller->forgotPassword($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($responseData)->toHaveKey('message', 'Password reset link sent to your email');
});

test('forgotPassword returns validation error when data is invalid', function () {
    $request = Request::create('/api/v1/auth/forgot-password', 'POST', [
        'email' => 'invalid-email',
    ]);

    $response = $this->controller->forgotPassword($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(422)
        ->and($responseData)->toHaveKey('message', 'Validation failed')
        ->and($responseData)->toHaveKey('errors');
});

test('forgotPassword handles user not found', function () {
    $request = Request::create('/api/v1/auth/forgot-password', 'POST', [
        'email' => 'nonexistent@example.com',
    ]);

    $this->authService->shouldReceive('forgotPassword')
        ->once()
        ->with('nonexistent@example.com')
        ->andReturn(false);

    $response = $this->controller->forgotPassword($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(404)
        ->and($responseData)->toHaveKey('message', 'We could not find a user with that email address');
});

// Reset Password Tests
test('resetPassword validates input correctly', function () {
    $request = Request::create('/api/v1/auth/reset-password', 'POST', [
        'token' => 'valid-token',
        'email' => 'test@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $this->authService->shouldReceive('resetPassword')
        ->once()
        ->andReturn(true);

    $response = $this->controller->resetPassword($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($responseData)->toHaveKey('message', 'Password has been reset successfully');
});

test('resetPassword returns validation error when data is invalid', function () {
    $request = Request::create('/api/v1/auth/reset-password', 'POST', [
        'token' => 'valid-token',
        'email' => 'invalid-email',
        'password' => 'short',
    ]);

    $response = $this->controller->resetPassword($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(422)
        ->and($responseData)->toHaveKey('message', 'Validation failed')
        ->and($responseData)->toHaveKey('errors');
});

test('resetPassword handles invalid token', function () {
    $request = Request::create('/api/v1/auth/reset-password', 'POST', [
        'token' => 'invalid-token',
        'email' => 'test@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $this->authService->shouldReceive('resetPassword')
        ->once()
        ->andReturn(false);

    $response = $this->controller->resetPassword($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(400)
        ->and($responseData)->toHaveKey('message', 'Invalid or expired password reset token');
});

// User Tests
test('user returns authenticated user', function () {
    $user = (object)['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com'];
    $request = Request::create('/api/v1/auth/user', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    $response = $this->controller->user($request);
    $responseData = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($responseData)->toEqual((array)$user);
});
