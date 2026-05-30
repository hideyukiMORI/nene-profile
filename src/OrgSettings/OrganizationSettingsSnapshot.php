<?php

declare(strict_types=1);

namespace NeneProfile\OrgSettings;

/**
 * Sanitized representation for API responses and audit snapshots.
 *
 * The `clear_bearer_token` VALUE is NEVER exposed — only a boolean indicating
 * whether one is configured. This applies to both API output and audit logs.
 */
final class OrganizationSettingsSnapshot
{
    /** @return array<string, mixed> */
    public static function toArray(OrganizationSettings $settings): array
    {
        return [
            'organization_id'        => $settings->organizationId,
            'default_encoding'       => $settings->defaultEncoding,
            'max_file_size_bytes'    => $settings->maxFileSizeBytes,
            'clear_bearer_token_set' => $settings->clearBearerToken !== null && $settings->clearBearerToken !== '',
        ];
    }
}
