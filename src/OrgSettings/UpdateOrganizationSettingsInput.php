<?php

declare(strict_types=1);

namespace NeneProfile\OrgSettings;

/**
 * Partial update input. A field left null means "leave unchanged", except
 * `clearBearerToken` which uses the explicit `clearBearerTokenProvided` flag so
 * a caller can clear the token by sending null.
 */
final readonly class UpdateOrganizationSettingsInput
{
    public function __construct(
        public int $organizationId,
        public ?string $defaultEncoding = null,
        public ?int $maxFileSizeBytes = null,
        public bool $clearBearerTokenProvided = false,
        public ?string $clearBearerToken = null,
    ) {
    }
}
