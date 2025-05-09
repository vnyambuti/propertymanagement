<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Property Management API',
    description: 'API for managing property leases, tenants, and units',
    contact: new OA\Contact(email: 'admin@example.com')
)]
#[OA\Server(
    url: 'http://localhost',
    description: 'Local Development Server'
)]
#[OA\Server(
    url: 'https://api.yourproduction.com',
    description: 'Production Server'
)]
class OpenApiConfig
{
    // This class serves only to hold the API documentation configuration
}
