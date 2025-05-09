<?php

namespace App\OpenApi\Schemas;

/**
 * Class UserSchema
 *
 * @package App\OpenApi\Schemas
 *
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="User model representation",
 *     required={"id", "name", "email", "role"},
 *     @OA\Property(
 *         property="id",
 *         title="ID",
 *         description="User unique identifier",
 *         type="integer",
 *         format="int64",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         title="Name",
 *         description="User's full name",
 *         type="string",
 *         example="John Doe"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         title="Email",
 *         description="User's email address",
 *         type="string",
 *         format="email",
 *         example="john.doe@example.com"
 *     ),
 *     @OA\Property(
 *         property="role",
 *         title="Role",
 *         description="User's role in the system",
 *         type="string",
 *         enum={"admin", "manager", "user"},
 *         example="manager"
 *     ),
 *     @OA\Property(
 *         property="email_verified_at",
 *         title="Email Verified At",
 *         description="Timestamp when the email was verified",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         example="2025-05-01T12:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         title="Created At",
 *         description="Creation timestamp",
 *         type="string",
 *         format="date-time",
 *         example="2025-05-01T12:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         title="Updated At",
 *         description="Last update timestamp",
 *         type="string",
 *         format="date-time",
 *         example="2025-05-01T12:00:00Z"
 *     )
 * )
 */
class UserSchema
{
}
