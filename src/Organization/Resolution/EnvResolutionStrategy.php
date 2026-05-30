<?php

declare(strict_types=1);

namespace NeneProfile\Organization\Resolution;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Resolves org slug from the ORG_SLUG environment variable.
 *
 * Default for single-org installs and local development.
 * Returns null when ORG_SLUG is empty.
 */
final readonly class EnvResolutionStrategy implements OrgResolutionStrategyInterface
{
    public function __construct(
        private ?string $orgSlug,
    ) {
    }

    public function resolve(ServerRequestInterface $request): ?string
    {
        return ($this->orgSlug !== null && $this->orgSlug !== '') ? $this->orgSlug : null;
    }
}
