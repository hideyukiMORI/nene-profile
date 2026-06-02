<?php

declare(strict_types=1);

namespace NeneProfile\Organization\Resolution;

use NeneProfile\Auth\AuthContext;
use NeneProfile\Organization\OrganizationNotResolvedException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Tenant-scope guard for handlers serving org-scoped resources. Returns the
 * organization resolved by OrgResolverMiddleware, or throws
 * OrganizationNotResolvedException (→ 400) when the request carries no tenant.
 * Centralizes the guard that every org-scoped handler previously inlined.
 */
final class OrgScope
{
    public static function requireId(ServerRequestInterface $request): int
    {
        $organizationId = AuthContext::resolvedOrganizationId($request);

        if ($organizationId === null) {
            throw new OrganizationNotResolvedException();
        }

        return $organizationId;
    }
}
