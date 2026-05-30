<?php

declare(strict_types=1);

namespace NeneProfile\Tests\OrgSettings;

use NeneProfile\OrgSettings\OrganizationSettings;
use NeneProfile\OrgSettings\OrganizationSettingsRepositoryInterface;

final class InMemoryOrganizationSettingsRepository implements OrganizationSettingsRepositoryInterface
{
    /** @var array<int, OrganizationSettings> */
    private array $store = [];

    public function findByOrganizationId(int $organizationId): ?OrganizationSettings
    {
        return $this->store[$organizationId] ?? null;
    }

    public function upsert(OrganizationSettings $settings): void
    {
        $now = date('Y-m-d H:i:s');
        $this->store[$settings->organizationId] = new OrganizationSettings(
            organizationId: $settings->organizationId,
            defaultEncoding: $settings->defaultEncoding,
            maxFileSizeBytes: $settings->maxFileSizeBytes,
            clearBearerToken: $settings->clearBearerToken,
            createdAt: $now,
            updatedAt: $now,
        );
    }
}
