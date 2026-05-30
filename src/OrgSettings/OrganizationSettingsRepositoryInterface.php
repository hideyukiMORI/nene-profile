<?php

declare(strict_types=1);

namespace NeneProfile\OrgSettings;

interface OrganizationSettingsRepositoryInterface
{
    public function findByOrganizationId(int $organizationId): ?OrganizationSettings;

    /** Insert or update the settings row for an organization. */
    public function upsert(OrganizationSettings $settings): void;
}
