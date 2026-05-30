<?php

declare(strict_types=1);

namespace NeneProfile\User;

use RuntimeException;

/**
 * Thrown when assigning a role that the API does not permit via user CRUD —
 * notably `superadmin`, which is provisioned out-of-band (seed/CLI), never
 * through the tenant-scoped user endpoints.
 */
final class RoleNotAssignableException extends RuntimeException
{
    public function __construct(string $role)
    {
        parent::__construct("Role cannot be assigned via this endpoint: {$role}");
    }
}
