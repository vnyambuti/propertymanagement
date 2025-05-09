<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;


#[OA\Schema(
    schema: 'MessageResponse',
    title: 'Message Response',
    description: 'Simple message response'
)]
class MessageResponse
{
    #[OA\Property(property: 'success', type: 'boolean', example: true)]
    public bool $success;

    #[OA\Property(property: 'message', type: 'string', example: 'Operation completed successfully')]
    public string $message;
}
