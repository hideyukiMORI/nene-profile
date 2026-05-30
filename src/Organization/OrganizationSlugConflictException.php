<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

use RuntimeException;

final class OrganizationSlugConflictException extends RuntimeException
{
    public function __construct(string $slug)
    {
        parent::__construct("Organization slug already exists: {$slug}");
    }
}
