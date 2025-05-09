<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Tenant',
    title: 'Tenant',
    description: 'Tenant model'
)]
class Tenant
{
    #[OA\Property(property: 'id', type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(property: 'first_name', type: 'string', example: 'John')]
    public string $first_name;

    #[OA\Property(property: 'last_name', type: 'string', example: 'Doe')]
    public string $last_name;

    #[OA\Property(property: 'email', type: 'string', format: 'email', example: 'john.doe@example.com')]
    public string $email;

    #[OA\Property(property: 'phone', type: 'string', example: '(555) 123-4567')]
    public string $phone;

    #[OA\Property(property: 'emergency_contact_name', type: 'string', example: 'Jane Doe')]
    public string $emergency_contact_name;

    #[OA\Property(property: 'emergency_contact_phone', type: 'string', example: '(555) 987-6543')]
    public string $emergency_contact_phone;

    #[OA\Property(
        property: 'status',
        type: 'string',
        enum: ['active', 'inactive', 'prospective'],
        example: 'active'
    )]
    public string $status;

    #[OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-05-09T12:00:00Z')]
    public string $created_at;

    #[OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-05-09T12:00:00Z')]
    public string $updated_at;
}
