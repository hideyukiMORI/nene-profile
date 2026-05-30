<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

interface CreateOrganizationUseCaseInterface
{
    public function execute(?int $actorUserId, CreateOrganizationInput $input): CreateOrganizationOutput;
}
