<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

interface UpdateOrganizationUseCaseInterface
{
    public function execute(?int $actorUserId, UpdateOrganizationInput $input): Organization;
}
