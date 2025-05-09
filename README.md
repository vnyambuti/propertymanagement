# Property Management System

A comprehensive real estate property management system built with Laravel, providing a robust API for managing properties, tenants, leases, maintenance requests, and payments.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Setup Instructions](#setup-instructions)
- [API Documentation](#api-documentation)
- [Architecture Overview](#architecture-overview)
- [Security Measures](#security-measures)
- [Caching Strategy](#caching-strategy)
- [Scaling Approach](#scaling-approach)
- [Testing](#testing)
- [License](#license)

## Overview

This property management system is designed to streamline the process of managing real estate properties, from tenant applications and lease management and payment tracking. The application provides a RESTful API built with Laravel, allowing for integration with various frontend applications or mobile apps.

## Features

- User authentication and role-based access control
- Property management (listing, categories, attributes)
- Tenant management
- Lease agreements and documents
- Payment processing and invoicing
- Notification system

## Setup Instructions

### Prerequisites

- PHP 8.1+
- Composer 2.0+
- MySQL 8.0+ or PostgreSQL 13+
- Node.js 16+ and NPM (for frontend assets)
- Redis (optional, for caching)

### Installation Steps

1. **Clone the repository**

```bash
git clone https://github.com/vnyambuti/propertymanagement.git
cd propertymanagement
```

2. **Install PHP dependencies**

```bash
composer install
```

3. **Set up environment variables**

```bash
cp .env.example .env
```

Edit the `.env` file with your database credentials and other configuration settings.

4. **Generate application key**

```bash
php artisan key:generate
```

5. **Run database migrations and seeders**

```bash
php artisan migrate
php artisan db:seed
```

6. **Install JavaScript dependencies and compile assets**

```bash
npm install
npm run dev
```

7. **Set up storage symlink**

```bash
php artisan storage:link
```

8. **Start the development server**

```bash
php artisan serve
```

The application should now be running at `http://localhost:8000`.


## API Documentation

### Accessing Documentation

The API documentation is available in two formats:

1. **Postman Collection**
   
   A Postman collection file is included in the repository at `/storage/private/2025_05_09_142911_postman.json`. Import this file into Postman to access all API endpoints with examples.

### Authentication

The API uses JWT (JSON Web Tokens) for authentication:

1. Register a new user or login with existing credentials at `/api/auth/login`
2. Use the returned token in the Authorization header for subsequent requests:
   `Authorization: Bearer {your_token_here}`

### API Endpoints Overview

| Resource | Endpoints |
|----------|-----------|
| Authentication | POST /api/auth/login, POST /api/auth/register, POST /api/auth/logout |
| Properties | GET /api/properties, POST /api/properties, GET /api/properties/{id}, PUT /api/properties/{id}, DELETE /api/properties/{id} |
| Tenants | GET /api/tenants, POST /api/tenants, GET /api/tenants/{id}, PUT /api/tenants/{id}, DELETE /api/tenants/{id} |
| Leases | GET /api/leases, POST /api/leases, GET /api/leases/{id}, PUT /api/leases/{id}, DELETE /api/leases/{id} |
| Payments | GET /api/payments, POST /api/payments, GET /api/payments/{id} |


## Architecture Overview

### Application Structure

The project follows a standard Laravel architecture with some additional patterns:

- **Repository Pattern**: Data access is abstracted through repository classes
- **Service Layer**: Business logic is contained in service classes
- **API Resources**: Response transformations use Laravel's API resources
- **Middleware**: Request filtering and validation through custom middleware
- **Events & Listeners**: For handling asynchronous processes like notifications

### Database Schema

The database follows a normalized structure with the following key entities:

- Users
- Properties
- PropertyTypes
- PropertyAttributes
- Tenants
- Leases
- MaintenanceRequests
- Payments
- Documents

### Directory Structure

```
app/
├── Console/          # Custom artisan commands
├── Exceptions/       # Exception handlers
├── Http/
│   ├── Controllers/  # API and web controllers
│   ├── Middleware/   # Request middleware
│   └── Resources/    # API resource transformers
├── Domain/
│   └── Property/
|       └──Models/    #Entities
├── Providers/        # Service providers
├── Repositories/     # Data access layer
├── Services/         # Business logic
└── Events/           # Event classes
```

## Security Measures

The application implements several security best practices:

1. **Authentication**:
   - JWT-based token authentication with configurable expiry
   - Role-based access control (RBAC)
   - Protection against brute force attacks with rate limiting

2. **Data Protection**:
   - Input validation using Laravel's form request validation
   - SQL injection prevention through parameterized queries
   - XSS protection with content filtering

3. **API Security**:
   - CORS configuration for controlled cross-origin access
   - API rate limiting to prevent abuse
   - Require HTTPS in production

4. **Sensitive Data**:
   - Encryption of sensitive tenant information
   - Secure storage of documents and lease agreements
   - Password hashing using bcrypt

5. **Auditing**:
   - Activity logging for administrative actions
   - Login attempt tracking

## Caching Strategy

The application implements a multi-layer caching approach:

1. **Application Cache**:
   - Property listings cached with automatic invalidation on updates
   - User permissions cached to reduce database queries
   - Configuration settings cached for performance

2. **Database Query Cache**:
   - Frequently accessed queries are cached using Laravel's query cache
   - Report data is cached with scheduled refresh intervals

3. **HTTP Caching**:
   - ETag support for API responses
   - Cache-Control headers for public resources

4. **Cache Drivers**:
   - Redis is the recommended cache driver for production
   - File-based caching available for development environments

5. **Cache Management**:
   - Artisan commands for managing cache
   - Automatic cache clearing for critical updates

## Scaling Approach

The application is designed with scalability in mind:

1. **Horizontal Scaling**:
   - Stateless API design allows for multiple server instances
   - Session storage in Redis enables load balancing

2. **Database Optimization**:
   - Indexing strategy for common query patterns
   - Database connection pooling
   - Read replicas can be configured for high-read scenarios

3. **Queue System**:
   - Resource-intensive tasks (report generation, notifications) use Laravel's queue system
   - Background processing for email sending and document generation

4. **Media Storage**:
   - Property images and documents use cloud storage (S3 compatible)
   - CDN integration for faster media delivery

5. **Monitoring & Performance**:
   - Integration with application performance monitoring (APM) tools
   - Query performance monitoring
   - Custom artisan commands for performance diagnostics

## Testing

The application includes comprehensive testing:

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run tests with coverage report
php artisan test --coverage
```

Test coverage includes:
- Unit tests for Auth and resource controllers
- Feature tests for Auth and resource controllers

## License

[MIT License](LICENSE.md)

---

Developed by [Victor Nyambuti](https://github.com/vnyambuti)
