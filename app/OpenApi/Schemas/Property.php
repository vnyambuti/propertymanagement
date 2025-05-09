<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Property',
    title: 'Property',
    description: 'Property model'
)]
class Property
{
    #[OA\Property(property: 'id', type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(property: 'name', type: 'string', example: 'Sunset Apartments')]
    public string $name;

    #[OA\Property(property: 'address', type: 'string', example: '123 Main Street')]
    public string $address;

    #[OA\Property(property: 'town', type: 'string', example: 'Springfield')]
    public string $town;

    #[OA\Property(property: 'county', type: 'string', example: 'Greenfield County')]
    public string $county;

    #[OA\Property(
        property: 'type',
        type: 'string',
        enum: ['apartment', 'house', 'commercial', 'condo', 'townhouse'],
        example: 'apartment'
    )]
    public string $type;

    #[OA\Property(property: 'user_id', type: 'integer', example: 10)]
    public int $user_id;

    #[OA\Property(
        property: 'units',
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/Unit')
    )]
    public array $units;

    #[OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-05-09T12:00:00Z')]
    public string $created_at;

    #[OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-05-09T12:00:00Z')]
    public string $updated_at;
}
