<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Unit',
    title: 'Unit',
    description: 'Property unit model'
)]
class Unit
{
    #[OA\Property(property: 'id', type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(property: 'property_id', type: 'integer', example: 1)]
    public int $property_id;

    #[OA\Property(property: 'unit_number', type: 'string', example: '101A')]
    public string $unit_number;

    #[OA\Property(property: 'type', type: 'string', example: 'apartment')]
    public string $type;

    #[OA\Property(property: 'bedrooms', type: 'integer', example: 2)]
    public int $bedrooms;

    #[OA\Property(property: 'bathrooms', type: 'number', format: 'float', example: 1.5)]
    public float $bathrooms;

    #[OA\Property(property: 'square_feet', type: 'integer', example: 1200)]
    public int $square_feet;

    #[OA\Property(
        property: 'status',
        type: 'string',
        enum: ['vacant', 'occupied', 'maintenance', 'reserved'],
        example: 'occupied'
    )]
    public string $status;

    #[OA\Property(
        property: 'features',
        type: 'array',
        items: new OA\Items(type: 'string'),
        example: ['balcony', 'hardwood floors']
    )]
    public array $features;

    #[OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-05-09T12:00:00Z')]
    public string $created_at;

    #[OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-05-09T12:00:00Z')]
    public string $updated_at;
}
