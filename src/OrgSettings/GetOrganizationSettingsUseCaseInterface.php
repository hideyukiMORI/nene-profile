<?php

declare(strict_types=1);

namespace NeneProfile\OrgSettings;

interface GetOrganizationSettingsUseCaseInterface
{
    public function execute(int $organizationId): OrganizationSettings;
}
