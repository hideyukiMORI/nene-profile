<?php

declare(strict_types=1);

namespace NeneProfile\OrgSettings;

interface UpdateOrganizationSettingsUseCaseInterface
{
    public function execute(?int $actorUserId, UpdateOrganizationSettingsInput $input): OrganizationSettings;
}
