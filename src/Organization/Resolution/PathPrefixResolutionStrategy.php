<?php

declare(strict_types=1);

namespace NeneProfile\Organization\Resolution;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Resolves org slug from URL path prefix: /{org-slug}/admin/... → "{org-slug}".
 *
 * Best for shared-host deployments where wildcard subdomains are not available.
 */
final readonly class PathPrefixResolutionStrategy implements OrgResolutionStrategyInterface
{
    public function resolve(ServerRequestInterface $request): ?string
    {
        $path = $request->getUri()->getPath();

        $trimmed = ltrim($path, '/');
        $parts = explode('/', $trimmed, 2);
        $candidate = $parts[0];

        return $candidate !== '' ? $candidate : null;
    }
}
