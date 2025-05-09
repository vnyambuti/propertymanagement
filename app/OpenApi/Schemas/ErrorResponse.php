<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;



#[OA\Schema(
    schema: 'ErrorResponse',
    title: 'Error Response',
    description: 'Error response with validation details'
)]
class ErrorResponse
{
    #[OA\Property(property: 'success', type: 'boolean', example: false)]
    public bool $success;

    #[OA\Property(
        property: 'errors',
        type: 'object',
        additionalProperties: [
            'type' => 'array',
            'items' => ['type' => 'string']
        ],
        example: ['name' => ['The name field is required.']]
    )]
    public object $errors;
}
