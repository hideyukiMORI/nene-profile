<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

use RuntimeException;

/**
 * Raised by a handler when an org-scoped admin route is reached without a
 * resolved tenant (OrgResolverMiddleware did not set nene2.org.id). Mapped to a
 * 400 'org-not-resolved' problem by OrganizationNotResolvedExceptionHandler.
 */
final class OrganizationNotResolvedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('This action requires an organization context.');
    }
}
