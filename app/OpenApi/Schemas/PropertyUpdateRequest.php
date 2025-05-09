<?php
namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;


#[OA\Schema(
    schema: 'PropertyUpdateRequest',
    title: 'Property Update Request',
    description: 'Property update request schema'
)]
class PropertyUpdateRequest
{
    #[OA\Property(property: 'name', type: 'string', example: 'Sunset Apartments Updated')]
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
}
