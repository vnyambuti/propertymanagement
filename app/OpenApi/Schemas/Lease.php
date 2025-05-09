<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Lease',
    title: 'Lease',
    description: 'Lease model'
)]
class Lease
{
    #[OA\Property(property: 'id', type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(property: 'unit_id', type: 'integer', example: 1)]
    public int $unit_id;

    #[OA\Property(property: 'tenant_id', type: 'integer', example: 1)]
    public int $tenant_id;

    #[OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2025-01-01')]
    public string $start_date;

    #[OA\Property(property: 'end_date', type: 'string', format: 'date', example: '2026-01-01')]
    public string $end_date;

    #[OA\Property(property: 'rent_amount', type: 'number', format: 'float', example: 1500.00)]
    public float $rent_amount;

    #[OA\Property(property: 'security_deposit', type: 'number', format: 'float', example: 3000.00)]
    public float $security_deposit;

    #[OA\Property(
        property: 'status',
        type: 'string',
        enum: ['active', 'pending', 'terminated', 'expired'],
        example: 'active'
    )]
    public string $status;

    #[OA\Property(property: 'notes', type: 'string', example: 'Monthly lease with option to renew', nullable: true)]
    public ?string $notes;

    #[OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-05-09T12:00:00Z')]
    public string $created_at;

    #[OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-05-09T12:00:00Z')]
    public string $updated_at;

    #[OA\Property(property: 'unit', ref: '#/components/schemas/Unit', nullable: true)]
    public ?object $unit;

    #[OA\Property(property: 'tenant', ref: '#/components/schemas/Tenant', nullable: true)]
    public ?object $tenant;
}
